package com.example

import android.os.Bundle
import androidx.activity.ComponentActivity
import androidx.activity.compose.setContent
import androidx.activity.enableEdgeToEdge
import androidx.compose.foundation.layout.fillMaxSize
import androidx.compose.material3.MaterialTheme
import androidx.compose.material3.Surface
import androidx.compose.runtime.collectAsState
import androidx.compose.runtime.getValue
import androidx.compose.ui.Modifier
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

class MainActivity : ComponentActivity() {
    override fun onCreate(savedInstanceState: Bundle?) {
        super.onCreate(savedInstanceState)
        enableEdgeToEdge()

        // Room database, DAO and Repository Injection patterns
        val database = StickerDatabase.getDatabase(applicationContext)
        val dao = database.stickerPackDao()
        val repository = StickerRepository(dao)
        val vmFactory = StickerViewModelFactory(repository)
        
        // Single centralized view model instance
        val viewModel = ViewModelProvider(this, vmFactory)[StickerViewModel::class.java]

        setContent {
            MyApplicationTheme {
                Surface(
                    modifier = Modifier.fillMaxSize(),
                    color = MaterialTheme.colorScheme.background
                ) {
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
