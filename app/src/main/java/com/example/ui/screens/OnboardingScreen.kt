package com.example.ui.screens

import androidx.compose.animation.AnimatedVisibility
import androidx.compose.animation.core.*
import androidx.compose.animation.fadeIn
import androidx.compose.animation.fadeOut
import androidx.compose.foundation.Image
import androidx.compose.foundation.background
import androidx.compose.foundation.clickable
import androidx.compose.foundation.layout.*
import androidx.compose.foundation.shape.CircleShape
import androidx.compose.foundation.shape.RoundedCornerShape
import androidx.compose.material3.*
import androidx.compose.runtime.*
import androidx.compose.ui.Alignment
import androidx.compose.ui.Modifier
import androidx.compose.ui.draw.blur
import androidx.compose.ui.draw.clip
import androidx.compose.ui.draw.rotate
import androidx.compose.ui.graphics.Brush
import androidx.compose.ui.graphics.Color
import androidx.compose.ui.graphics.graphicsLayer
import androidx.compose.ui.layout.ContentScale
import androidx.compose.ui.platform.LocalContext
import androidx.compose.ui.platform.testTag
import androidx.compose.ui.text.font.FontWeight
import androidx.compose.ui.text.style.TextAlign
import androidx.compose.ui.text.style.TextDecoration
import androidx.compose.ui.unit.dp
import androidx.compose.ui.unit.sp
import coil.compose.rememberAsyncImagePainter
import com.example.data.model.MockData
import com.example.ui.viewmodel.Screen
import com.example.ui.viewmodel.StickerViewModel
import kotlinx.coroutines.delay

@Composable
fun OnboardingScreen(viewModel: StickerViewModel) {
    val context = LocalContext.current
    var currentSlide by remember { mutableIntStateOf(0) }
    val totalSlides = 2

    // Auto-sliding loop
    LaunchedEffect(Unit) {
        while (true) {
            delay(4000)
            currentSlide = (currentSlide + 1) % totalSlides
        }
    }

    // Infinite translation float animations
    val infiniteTransition = rememberInfiniteTransition(label = "StickerFloat")
    val floatOffset1 by infiniteTransition.animateFloat(
        initialValue = -10f,
        targetValue = 10f,
        animationSpec = infiniteRepeatable(
            animation = tween(3000, easing = EaseInOutSine),
            repeatMode = RepeatMode.Reverse
        ),
        label = "Float1"
    )
    val floatRotation by infiniteTransition.animateFloat(
        initialValue = -5f,
        targetValue = 5f,
        animationSpec = infiniteRepeatable(
            animation = tween(4000, easing = EaseInOutQuad),
            repeatMode = RepeatMode.Reverse
        ),
        label = "Rotation"
    )

    val slide0Alpha by animateFloatAsState(
        targetValue = if (currentSlide == 0) 1f else 0f,
        animationSpec = tween(500),
        label = "Alpha0"
    )
    val slide1Alpha by animateFloatAsState(
        targetValue = if (currentSlide == 1) 1f else 0f,
        animationSpec = tween(500),
        label = "Alpha1"
    )

    Box(
        modifier = Modifier
            .fillMaxSize()
            .background(MaterialTheme.colorScheme.background)
    ) {
        // Floating Top Skip Button
        Text(
            text = "Pular",
            color = MaterialTheme.colorScheme.onSurfaceVariant,
            fontWeight = FontWeight.SemiBold,
            fontSize = 14.sp,
            modifier = Modifier
                .align(Alignment.TopEnd)
                .statusBarsPadding()
                .padding(20.dp)
                .clip(RoundedCornerShape(20.dp))
                .background(MaterialTheme.colorScheme.surface.copy(alpha = 0.5f))
                .clickable { viewModel.completeOnboardingAndLogin() }
                .padding(horizontal = 16.dp, vertical = 8.dp)
                .testTag("skip_onboarding_btn")
        )

        // Radial glow backgrounds
        Box(
            modifier = Modifier
                .size(300.dp)
                .align(Alignment.Center)
                .graphicsLayer(translationY = -120f)
                .blur(80.dp)
                .background(
                    Brush.radialGradient(
                        colors = listOf(
                            MaterialTheme.colorScheme.primary.copy(alpha = 0.15f),
                            Color.Transparent
                        )
                    ),
                    shape = CircleShape
                )
        )

        // Main content column containing graphic cards, texts, dots and sign-in key button
        Column(
            modifier = Modifier
                .fillMaxSize()
                .padding(20.dp)
                .navigationBarsPadding(),
            verticalArrangement = Arrangement.SpaceBetween,
            horizontalAlignment = Alignment.CenterHorizontally
        ) {
            Spacer(modifier = Modifier.height(60.dp))

            // Floating Custom Stickers Composing Scene
            Box(
                modifier = Modifier
                    .fillMaxWidth()
                    .weight(1.1f)
                    .graphicsLayer(translationY = floatOffset1),
                contentAlignment = Alignment.Center
            ) {
                // Secondary Left Sticker (Rocket illustration)
                Card(
                    modifier = Modifier
                        .size(110.dp)
                        .align(Alignment.BottomStart)
                        .graphicsLayer(translationX = 20f, translationY = -40f)
                        .rotate(-15f + floatRotation),
                    shape = RoundedCornerShape(16.dp),
                    colors = CardDefaults.cardColors(containerColor = Color.White),
                    elevation = CardDefaults.cardElevation(8.dp)
                ) {
                    Image(
                        painter = rememberAsyncImagePainter(MockData.onboardingHeroStickerUrl),
                        contentDescription = "Cute Character Sticker Decorator",
                        contentScale = ContentScale.Crop,
                        modifier = Modifier.fillMaxSize()
                    )
                }

                // Main Floating Core Sticker Artwork
                Card(
                    modifier = Modifier
                        .size(180.dp)
                        .rotate(floatRotation)
                        .align(Alignment.Center),
                    shape = RoundedCornerShape(24.dp),
                    colors = CardDefaults.cardColors(containerColor = Color.White),
                    elevation = CardDefaults.cardElevation(16.dp)
                ) {
                    Box(modifier = Modifier.fillMaxSize()) {
                        Image(
                            painter = rememberAsyncImagePainter(MockData.cyberGatosStickers[4].imageUrl),
                            contentDescription = "Waving happy cute cat",
                            contentScale = ContentScale.Crop,
                            modifier = Modifier.fillMaxSize()
                        )
                    }
                }

                // Right Top Sticker (Holographic Abstract design)
                Card(
                    modifier = Modifier
                        .size(90.dp)
                        .align(Alignment.TopEnd)
                        .graphicsLayer(translationX = -20f, translationY = 30f)
                        .rotate(12f - floatRotation),
                    shape = RoundedCornerShape(12.dp),
                    colors = CardDefaults.cardColors(containerColor = Color.White),
                    elevation = CardDefaults.cardElevation(6.dp)
                ) {
                    Image(
                        painter = rememberAsyncImagePainter(MockData.retroStickers[1].imageUrl),
                        contentDescription = "Retro Tape Cassette sticker decoration",
                        contentScale = ContentScale.Crop,
                        modifier = Modifier.fillMaxSize()
                    )
                }

                // Right Bottom Small Emojis list sticker
                Card(
                    modifier = Modifier
                        .size(80.dp)
                        .align(Alignment.BottomEnd)
                        .graphicsLayer(translationX = -30f, translationY = -10f)
                        .rotate(6f + floatRotation),
                    shape = RoundedCornerShape(12.dp),
                    colors = CardDefaults.cardColors(containerColor = Color.White),
                    elevation = CardDefaults.cardElevation(4.dp)
                ) {
                    Image(
                        painter = rememberAsyncImagePainter(MockData.popularPacks[3].stickers[0].imageUrl),
                        contentDescription = "Alien logo sticker emoji sticker",
                        contentScale = ContentScale.Crop,
                        modifier = Modifier.fillMaxSize()
                    )
                }
            }

            // Slider Text Box with sliding animation
            Box(
                modifier = Modifier
                    .fillMaxWidth()
                    .weight(0.5f),
                contentAlignment = Alignment.Center
            ) {
                // Slide 0 description text with custom alpha opacity transition
                Column(
                    modifier = Modifier
                        .fillMaxWidth()
                        .graphicsLayer(alpha = slide0Alpha),
                    horizontalAlignment = Alignment.CenterHorizontally
                ) {
                    Text(
                        text = "Crie seus próprios stickers",
                        style = MaterialTheme.typography.headlineLarge.copy(
                            fontWeight = FontWeight.Bold,
                            color = MaterialTheme.colorScheme.onSurface,
                            fontSize = 28.sp,
                            lineHeight = 36.sp,
                            textAlign = TextAlign.Center
                        )
                    )
                    Spacer(modifier = Modifier.height(12.dp))
                    Text(
                        text = "Transforme suas fotos em figurinhas incríveis em segundos.",
                        style = MaterialTheme.typography.bodyLarge.copy(
                            color = MaterialTheme.colorScheme.onSurfaceVariant,
                            textAlign = TextAlign.Center,
                            fontSize = 16.sp
                        ),
                        modifier = Modifier.width(300.dp)
                    )
                }

                // Slide 1 description text with custom alpha opacity transition
                Column(
                    modifier = Modifier
                        .fillMaxWidth()
                        .graphicsLayer(alpha = slide1Alpha),
                    horizontalAlignment = Alignment.CenterHorizontally
                ) {
                    Text(
                        text = "Milhares de packs exclusivos",
                        style = MaterialTheme.typography.headlineLarge.copy(
                            fontWeight = FontWeight.Bold,
                            color = MaterialTheme.colorScheme.onSurface,
                            fontSize = 28.sp,
                            lineHeight = 36.sp,
                            textAlign = TextAlign.Center
                        )
                    )
                    Spacer(modifier = Modifier.height(12.dp))
                    Text(
                        text = "Explore coleções incríveis criadas pelos maiores artistas do mundo.",
                        style = MaterialTheme.typography.bodyLarge.copy(
                            color = MaterialTheme.colorScheme.onSurfaceVariant,
                            textAlign = TextAlign.Center,
                            fontSize = 16.sp
                        ),
                        modifier = Modifier.width(300.dp)
                    )
                }
            }

            // Paginator Index Dots
            Row(
                horizontalArrangement = Arrangement.Center,
                verticalAlignment = Alignment.CenterVertically,
                modifier = Modifier.padding(vertical = 12.dp)
            ) {
                repeat(totalSlides) { index ->
                    val isSelected = currentSlide == index
                    val dotWidth by animateDpAsState(
                        targetValue = if (isSelected) 24.dp else 8.dp,
                        animationSpec = spring(dampingRatio = Spring.DampingRatioMediumBouncy),
                        label = "DotWidth"
                    )
                    Box(
                        modifier = Modifier
                            .padding(horizontal = 4.dp)
                            .height(8.dp)
                            .width(dotWidth)
                            .clip(CircleShape)
                            .background(
                                if (isSelected) MaterialTheme.colorScheme.primary
                                else MaterialTheme.colorScheme.outlineVariant
                            )
                            .clickable { currentSlide = index }
                    )
                }
            }

            // Login & Google Sign In Trigger Action button
            Column(
                modifier = Modifier
                    .fillMaxWidth()
                    .padding(bottom = 12.dp),
                horizontalAlignment = Alignment.CenterHorizontally
            ) {
                Button(
                    onClick = { viewModel.completeOnboardingAndLogin() },
                    shape = RoundedCornerShape(100.dp),
                    colors = ButtonDefaults.buttonColors(
                        containerColor = MaterialTheme.colorScheme.primary
                    ),
                    modifier = Modifier
                        .fillMaxWidth()
                        .height(56.dp)
                        .testTag("google_login_button"),
                    elevation = ButtonDefaults.buttonElevation(8.dp)
                ) {
                    Row(
                        verticalAlignment = Alignment.CenterVertically,
                        horizontalArrangement = Arrangement.Center
                    ) {
                        Card(
                            modifier = Modifier.size(24.dp),
                            shape = CircleShape,
                            colors = CardDefaults.cardColors(containerColor = Color.White)
                        ) {
                            Box(
                                modifier = Modifier.fillMaxSize(),
                                contentAlignment = Alignment.Center
                            ) {
                                Text(
                                    text = "G",
                                    color = Color(0xFF4285F4),
                                    fontWeight = FontWeight.ExtraBold,
                                    fontSize = 14.sp
                                )
                            }
                        }
                        Spacer(modifier = Modifier.width(12.dp))
                        Text(
                            text = "Login com o Google",
                            color = Color.White,
                            fontSize = 16.sp,
                            fontWeight = FontWeight.Bold
                        )
                    }
                }

                Spacer(modifier = Modifier.height(16.dp))

                // Privacy legal footer links
                Text(
                    text = "Ao continuar, você concorda com nossos Termos de Serviço e Privacidade.",
                    style = MaterialTheme.typography.labelSmall.copy(
                        color = MaterialTheme.colorScheme.onSurfaceVariant,
                        fontSize = 11.sp,
                        textAlign = TextAlign.Center
                    ),
                    modifier = Modifier.padding(horizontal = 24.dp)
                )
            }
        }
    }
}
