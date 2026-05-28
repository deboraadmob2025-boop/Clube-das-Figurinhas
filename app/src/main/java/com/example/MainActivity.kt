package com.example

import android.content.ClipData
import android.content.ClipboardManager
import android.content.Context
import android.os.Bundle
import android.util.Log
import android.widget.Toast
import androidx.activity.ComponentActivity
import androidx.activity.compose.setContent
import androidx.activity.enableEdgeToEdge
import androidx.compose.foundation.background
import androidx.compose.foundation.border
import androidx.compose.foundation.layout.*
import androidx.compose.foundation.rememberScrollState
import androidx.compose.foundation.shape.RoundedCornerShape
import androidx.compose.foundation.verticalScroll
import androidx.compose.material3.*
import androidx.compose.runtime.*
import androidx.compose.ui.Alignment
import androidx.compose.ui.Modifier
import androidx.compose.ui.graphics.Color
import androidx.compose.ui.text.font.FontFamily
import androidx.compose.ui.text.font.FontWeight
import androidx.compose.ui.unit.dp
import androidx.compose.ui.unit.sp
import androidx.lifecycle.ViewModelProvider
import com.example.data.local.StickerDatabase
import com.example.data.repo.StickerRepository
import com.example.ui.screens.EditorScreen
import com.example.ui.screens.ExploreScreen
import com.example.ui.screens.MyPacksScreen
import com.example.ui.screens.OnboardingScreen
import com.example.ui.screens.ProfileScreen
import com.example.ui.theme.MyApplicationTheme
import com.example.ui.viewmodel.Screen
import com.example.ui.viewmodel.StickerViewModel
import com.example.ui.viewmodel.StickerViewModelFactory
import java.io.File
import java.io.PrintWriter
import java.io.StringWriter

class MainActivity : ComponentActivity() {
    override fun onCreate(savedInstanceState: Bundle?) {
        // Setup global unhandled exception handler to log and prevent silent crashes
        val oldHandler = Thread.getDefaultUncaughtExceptionHandler()
        Thread.setDefaultUncaughtExceptionHandler { thread, throwable ->
            try {
                val file = File(cacheDir, "crash_log.txt")
                val sw = StringWriter()
                val pw = PrintWriter(sw)
                throwable.printStackTrace(pw)
                file.writeText("Thread: ${thread.name}\n\n$sw")
                Log.e("FATAL_APP_CRASH", "Uncaught exception on thread ${thread.name}", throwable)
            } catch (e: Exception) {
                Log.e("FATAL_APP_CRASH", "Failed to write crash log", e)
            }
            oldHandler?.uncaughtException(thread, throwable)
        }

        super.onCreate(savedInstanceState)
        enableEdgeToEdge()

        // Read diagnostic crash log if exists
        val crashLogFile = File(cacheDir, "crash_log.txt")
        var initialCrashLogText: String? = null
        if (crashLogFile.exists()) {
            try {
                initialCrashLogText = crashLogFile.readText()
            } catch (e: Exception) {
                Log.e("CRASH_DASHBOARD", "Could not read crash log", e)
            }
        }

        // Room database, DAO and Repository Injection patterns
        val database = StickerDatabase.getDatabase(applicationContext)
        val dao = database.stickerPackDao()
        val repository = StickerRepository(dao)
        val vmFactory = StickerViewModelFactory(repository)
        
        // Single centralized view model instance
        val viewModel = ViewModelProvider(this, vmFactory)[StickerViewModel::class.java]

        setContent {
            MyApplicationTheme {
                var crashLogText by remember { mutableStateOf(initialCrashLogText) }

                Surface(
                    modifier = Modifier.fillMaxSize(),
                    color = MaterialTheme.colorScheme.background
                ) {
                    if (crashLogText != null) {
                        // Diagnostic developer preview page if app cracked earlier
                        CrashDiagnosticView(
                            errorText = crashLogText!!,
                            onClear = {
                                try {
                                    if (crashLogFile.exists()) {
                                        crashLogFile.delete()
                                    }
                                } catch (e: Exception) {
                                    Log.e("CRASH_DASHBOARD", "Could not delete crash file", e)
                                }
                                crashLogText = null
                            }
                        )
                    } else {
                        val currentScreen by viewModel.currentScreen.collectAsState()

                        // Reactive state-driven navigation selector
                        when (currentScreen) {
                            Screen.Onboarding -> OnboardingScreen(viewModel)
                            Screen.Explore -> ExploreScreen(viewModel)
                            Screen.Editor -> EditorScreen(viewModel)
                            Screen.MyPacks -> MyPacksScreen(viewModel)
                            Screen.Profile -> ProfileScreen(viewModel)
                        }
                    }
                }
            }
        }
    }

    @Composable
    private fun CrashDiagnosticView(errorText: String, onClear: () -> Unit) {
        Column(
            modifier = Modifier
                .fillMaxSize()
                .background(Color(0xFF111218))
                .padding(24.dp)
                .statusBarsPadding()
                .navigationBarsPadding(),
            verticalArrangement = Arrangement.SpaceBetween
        ) {
            Column(modifier = Modifier.weight(1f)) {
                Text(
                    text = "Alerta de Falha Técnica 🛡️",
                    color = Color(0xFFFF5252),
                    fontSize = 22.sp,
                    fontWeight = FontWeight.ExtraBold
                )
                
                Spacer(modifier = Modifier.height(8.dp))
                
                Text(
                    text = "O aplicativo parou de funcionar de forma inesperada. Capturamos o relatório técnico abaixo para ajudar na resolução com precisão cirúrgica:",
                    color = Color.LightGray,
                    fontSize = 13.sp,
                    fontWeight = FontWeight.Medium
                )

                Spacer(modifier = Modifier.height(16.dp))

                Box(
                    modifier = Modifier
                        .fillMaxWidth()
                        .weight(1f)
                        .background(Color(0xFF1E2129), RoundedCornerShape(16.dp))
                        .border(1.dp, Color.White.copy(alpha = 0.08f), RoundedCornerShape(16.dp))
                        .padding(16.dp)
                ) {
                    val scrollState = rememberScrollState()
                    Text(
                        text = errorText,
                        color = Color(0xFFFF8A80),
                        fontFamily = FontFamily.Monospace,
                        fontSize = 11.sp,
                        modifier = Modifier
                            .fillMaxSize()
                            .verticalScroll(scrollState)
                    )
                }
            }

            Spacer(modifier = Modifier.height(16.dp))

            Row(
                modifier = Modifier.fillMaxWidth(),
                horizontalArrangement = Arrangement.spacedBy(12.dp)
            ) {
                OutlinedButton(
                    onClick = {
                        val clipboard = getSystemService(Context.CLIPBOARD_SERVICE) as ClipboardManager
                        val clip = ClipData.newPlainText("Crash Stacktrace", errorText)
                        clipboard.setPrimaryClip(clip)
                        Toast.makeText(this@MainActivity, "Copiado para a área de transferência!", Toast.LENGTH_SHORT).show()
                    },
                    modifier = Modifier.weight(1f),
                    shape = RoundedCornerShape(100.dp),
                    colors = ButtonDefaults.outlinedButtonColors(contentColor = Color.White)
                ) {
                    Text("Copiar Detalhes", fontWeight = FontWeight.Bold, fontSize = 13.sp)
                }

                Button(
                    onClick = onClear,
                    modifier = Modifier.weight(1f),
                    shape = RoundedCornerShape(100.dp),
                    colors = ButtonDefaults.buttonColors(containerColor = MaterialTheme.colorScheme.primary)
                ) {
                    Text("Ignorar e Iniciar", fontWeight = FontWeight.Bold, color = Color.White, fontSize = 13.sp)
                }
            }
        }
    }
}
