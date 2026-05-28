package com.example.ui.screens

import android.widget.Toast
import androidx.compose.foundation.Image
import androidx.compose.foundation.background
import androidx.compose.foundation.clickable
import androidx.compose.foundation.layout.*
import androidx.compose.foundation.rememberScrollState
import androidx.compose.foundation.shape.CircleShape
import androidx.compose.foundation.shape.RoundedCornerShape
import androidx.compose.foundation.verticalScroll
import androidx.compose.material.icons.Icons
import androidx.compose.material.icons.filled.KeyboardArrowRight
import androidx.compose.material3.*
import androidx.compose.runtime.Composable
import androidx.compose.runtime.collectAsState
import androidx.compose.runtime.getValue
import androidx.compose.ui.Alignment
import androidx.compose.ui.Modifier
import androidx.compose.ui.draw.clip
import androidx.compose.ui.graphics.Brush
import androidx.compose.ui.graphics.Color
import androidx.compose.ui.layout.ContentScale
import androidx.compose.ui.platform.LocalContext
import androidx.compose.ui.platform.testTag
import androidx.compose.ui.text.font.FontWeight
import androidx.compose.ui.text.style.TextAlign
import androidx.compose.ui.unit.dp
import androidx.compose.ui.unit.sp
import coil.compose.rememberAsyncImagePainter
import com.example.ui.viewmodel.Screen
import com.example.ui.viewmodel.StickerViewModel

@Composable
fun ProfileScreen(viewModel: StickerViewModel) {
    val context = LocalContext.current
    val avatar by viewModel.userAvatar.collectAsState()
    val name by viewModel.userName.collectAsState()
    val isPremium by viewModel.isPremiumMember.collectAsState()

    val goldGradient = Brush.linearGradient(
        colors = listOf(Color(0xFFFFD700), Color(0xFFFFA500))
    )

    Box(
        modifier = Modifier
            .fillMaxSize()
            .background(MaterialTheme.colorScheme.background)
    ) {
        Column(
            modifier = Modifier
                .fillMaxSize()
                .verticalScroll(rememberScrollState())
                .padding(bottom = 100.dp),
            horizontalAlignment = Alignment.CenterHorizontally
        ) {
            // Profile Top appbar header frame
            Spacer(modifier = Modifier.height(32.dp))
            Row(
                modifier = Modifier
                    .fillMaxWidth()
                    .padding(horizontal = 20.dp),
                horizontalArrangement = Arrangement.SpaceBetween,
                verticalAlignment = Alignment.CenterVertically
            ) {
                Text(
                    text = "Perfil",
                    fontSize = 24.sp,
                    fontWeight = FontWeight.ExtraBold,
                    color = MaterialTheme.colorScheme.primary
                )
                Surface(
                    modifier = Modifier.size(40.dp),
                    shape = CircleShape,
                    color = MaterialTheme.colorScheme.surfaceVariant.copy(alpha = 0.4f)
                ) {
                    Box(contentAlignment = Alignment.Center, modifier = Modifier.clickable {
                        Toast.makeText(context, "Sincronização ajustada com sucesso!", Toast.LENGTH_SHORT).show()
                    }) {
                        Text("⚙️", fontSize = 18.sp)
                    }
                }
            }

            Spacer(modifier = Modifier.height(24.dp))

            // Studio Portrait Headshot with Edit button overlapping
            Box(modifier = Modifier.size(108.dp)) {
                Surface(
                    modifier = Modifier
                        .size(100.dp)
                        .testTag("profile_avatar"),
                    shape = CircleShape,
                    color = MaterialTheme.colorScheme.surfaceVariant,
                    tonalElevation = 4.dp
                ) {
                    Image(
                        painter = rememberAsyncImagePainter(avatar),
                        contentDescription = "Avatar picture portrait",
                        contentScale = ContentScale.Crop,
                        modifier = Modifier.fillMaxSize()
                    )
                }

                // Edit badge overlap float
                Surface(
                    modifier = Modifier
                        .size(32.dp)
                        .align(Alignment.BottomEnd)
                        .clickable {
                            Toast.makeText(context, "Editar foto de perfil...", Toast.LENGTH_SHORT).show()
                        },
                    shape = CircleShape,
                    color = MaterialTheme.colorScheme.primary,
                    tonalElevation = 2.dp,
                    shadowElevation = 2.dp
                ) {
                    Box(contentAlignment = Alignment.Center) {
                        Text("✏️", fontSize = 12.sp, color = Color.White)
                    }
                }
            }

            Spacer(modifier = Modifier.height(12.dp))

            Text(
                text = name,
                fontSize = 22.sp,
                fontWeight = FontWeight.ExtraBold,
                color = MaterialTheme.colorScheme.onSurface
            )
            Text(
                text = "@alex_stickers",
                fontSize = 14.sp,
                color = MaterialTheme.colorScheme.onSurfaceVariant
            )

            Spacer(modifier = Modifier.height(24.dp))

            // Go Premium Banner
            Surface(
                modifier = Modifier
                    .fillMaxWidth()
                    .padding(horizontal = 20.dp)
                    .clickable {
                        viewModel.togglePremium()
                        Toast.makeText(
                            context,
                            if (viewModel.isPremiumMember.value) "Assinatura PREMIUM Ativada!" else "Assinatura redefinida para Gratuita.",
                            Toast.LENGTH_SHORT
                        ).show()
                    }
                    .testTag("premium_banner"),
                shape = RoundedCornerShape(24.dp),
                color = Color.Transparent,
                shadowElevation = 8.dp
            ) {
                Box(
                    modifier = Modifier
                        .background(goldGradient)
                        .padding(20.dp)
                ) {
                    Row(
                        modifier = Modifier.fillMaxWidth(),
                        horizontalArrangement = Arrangement.SpaceBetween,
                        verticalAlignment = Alignment.CenterVertically
                    ) {
                        Column {
                            Row(verticalAlignment = Alignment.CenterVertically) {
                                Text("👑", fontSize = 16.sp)
                                Spacer(modifier = Modifier.width(6.dp))
                                Text(
                                    text = if (isPremium) "ASSINANTE PREMIUM" else "MEMBRO PREMIUM",
                                    color = Color.White.copy(alpha = 0.85f),
                                    fontWeight = FontWeight.ExtraBold,
                                    fontSize = 11.sp,
                                    letterSpacing = 1.sp
                                )
                            }
                            Spacer(modifier = Modifier.height(4.dp))
                            Text(
                                text = "Go Premium",
                                color = Color.White,
                                fontWeight = FontWeight.Black,
                                fontSize = 22.sp
                            )
                            Text(
                                text = if (isPremium) "Acesso ilimitado ativado" else "Desbloqueie figurinhas animadas premium e IA.",
                                color = Color.White.copy(alpha = 0.9f),
                                fontSize = 12.sp,
                                fontWeight = FontWeight.Medium
                            )
                        }

                        Icon(
                            imageVector = Icons.Default.KeyboardArrowRight,
                            contentDescription = "Upgrade Premium",
                            tint = Color.White,
                            modifier = Modifier.size(32.dp)
                        )
                    }
                }
            }

            Spacer(modifier = Modifier.height(24.dp))

            // Stats Bento Grid (12 My Packs, 1.2k Downloads, 482 Likes)
            Row(
                modifier = Modifier
                    .fillMaxWidth()
                    .padding(horizontal = 20.dp),
                horizontalArrangement = Arrangement.spacedBy(12.dp)
            ) {
                BentoStatBox(
                    modifier = Modifier.weight(1f),
                    statCount = "12",
                    statLabel = "Meus Packs"
                )
                BentoStatBox(
                    modifier = Modifier.weight(1f),
                    statCount = "1.2k",
                    statLabel = "Downloads"
                )
                BentoStatBox(
                    modifier = Modifier.weight(1f),
                    statCount = "482",
                    statLabel = "Curtidas"
                )
            }

            Spacer(modifier = Modifier.height(24.dp))

            // Account settings list cards
            Column(
                modifier = Modifier
                    .fillMaxWidth()
                    .padding(horizontal = 20.dp)
            ) {
                Text(
                    text = "CONFIGURAÇÕES DA CONTA",
                    color = MaterialTheme.colorScheme.onSurfaceVariant,
                    fontSize = 11.sp,
                    fontWeight = FontWeight.ExtraBold,
                    letterSpacing = 1.sp,
                    modifier = Modifier.padding(start = 8.dp, bottom = 8.dp)
                )

                Surface(
                    shape = RoundedCornerShape(20.dp),
                    color = MaterialTheme.colorScheme.surfaceVariant.copy(alpha = 0.15f),
                    modifier = Modifier.fillMaxWidth()
                ) {
                    Column {
                        AccountSettingsItem(
                            icon = "🌐",
                            title = "Idioma",
                            subValue = "Português (Brasil)",
                            onClick = { Toast.makeText(context, "Idioma alterado para Português!", Toast.LENGTH_SHORT).show() }
                        )
                        Divider(color = Color.White.copy(alpha = 0.05f))
                        AccountSettingsItem(
                            icon = "🔔",
                            title = "Notificações",
                            subValue = "Push, Email e SMS Ativos",
                            onClick = { }
                        )
                        Divider(color = Color.White.copy(alpha = 0.05f))
                        AccountSettingsItem(
                            icon = "💳",
                            title = "Plano de Assinatura",
                            subValue = if (isPremium) "Plano Premium Ativo ($1.99)" else "Free Tier (Gratuito)",
                            onClick = { viewModel.togglePremium() }
                        )
                        Divider(color = Color.White.copy(alpha = 0.05f))
                        AccountSettingsItem(
                            icon = "❓",
                            title = "Ajuda & Suporte",
                            subValue = "Dúvidas frequentes e chat online",
                            onClick = { }
                        )
                    }
                }
            }

            Spacer(modifier = Modifier.height(32.dp))

            // Logout egress call button
            Button(
                onClick = {
                    viewModel.navigateTo(Screen.Onboarding)
                },
                modifier = Modifier
                    .fillMaxWidth()
                    .padding(horizontal = 20.dp)
                    .height(52.dp)
                    .testTag("logout_profile_btn"),
                shape = RoundedCornerShape(16.dp),
                colors = ButtonDefaults.buttonColors(
                    containerColor = MaterialTheme.colorScheme.surfaceVariant.copy(alpha = 0.3f)
                )
            ) {
                Row(verticalAlignment = Alignment.CenterVertically) {
                    Text("🚪", fontSize = 16.sp)
                    Spacer(modifier = Modifier.width(8.dp))
                    Text(
                        text = "Sair da Conta",
                        color = Color.Red,
                        fontSize = 14.sp,
                        fontWeight = FontWeight.Bold
                    )
                }
            }

            Spacer(modifier = Modifier.height(16.dp))
            Text(
                text = "v2.4.0 (Edição 2026)",
                fontSize = 12.sp,
                color = MaterialTheme.colorScheme.onSurfaceVariant.copy(alpha = 0.5f),
                textAlign = TextAlign.Center
            )
        }

        BottomNavigationBar(viewModel, currentActiveScreen = Screen.Profile)
    }
}

@Composable
fun BentoStatBox(modifier: Modifier = Modifier, statCount: String, statLabel: String) {
    Surface(
        modifier = modifier.height(80.dp),
        shape = RoundedCornerShape(16.dp),
        color = MaterialTheme.colorScheme.surfaceVariant.copy(alpha = 0.15f),
        border = androidx.compose.foundation.BorderStroke(1.dp, Color.White.copy(alpha = 0.05f))
    ) {
        Column(
            modifier = Modifier.padding(12.dp),
            horizontalAlignment = Alignment.CenterHorizontally,
            verticalArrangement = Arrangement.Center
        ) {
            Text(
                text = statCount,
                color = MaterialTheme.colorScheme.primary,
                fontSize = 20.sp,
                fontWeight = FontWeight.ExtraBold
            )
            Spacer(modifier = Modifier.height(4.dp))
            Text(
                text = statLabel,
                color = MaterialTheme.colorScheme.onSurfaceVariant,
                fontSize = 11.sp,
                fontWeight = FontWeight.SemiBold
            )
        }
    }
}

@Composable
fun AccountSettingsItem(icon: String, title: String, subValue: String, onClick: () -> Unit) {
    Row(
        modifier = Modifier
            .fillMaxWidth()
            .clickable(onClick = onClick)
            .padding(16.dp),
        verticalAlignment = Alignment.CenterVertically,
        horizontalArrangement = Arrangement.SpaceBetween
    ) {
        Row(verticalAlignment = Alignment.CenterVertically) {
            Text(icon, fontSize = 20.sp)
            Spacer(modifier = Modifier.width(16.dp))
            Column {
                Text(text = title, color = MaterialTheme.colorScheme.onSurface, fontSize = 15.sp, fontWeight = FontWeight.Bold)
                Text(text = subValue, color = MaterialTheme.colorScheme.onSurfaceVariant, fontSize = 11.sp)
            }
        }

        Icon(
            imageVector = Icons.Default.KeyboardArrowRight,
            contentDescription = null,
            tint = MaterialTheme.colorScheme.onSurfaceVariant.copy(alpha = 0.6f)
        )
    }
}
