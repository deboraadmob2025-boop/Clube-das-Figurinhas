package com.example.ui.screens

import android.widget.Toast
import androidx.compose.foundation.Image
import androidx.compose.foundation.background
import androidx.compose.foundation.clickable
import androidx.compose.foundation.layout.*
import androidx.compose.foundation.lazy.LazyColumn
import androidx.compose.foundation.lazy.LazyRow
import androidx.compose.foundation.lazy.items
import androidx.compose.foundation.shape.CircleShape
import androidx.compose.foundation.shape.RoundedCornerShape
import androidx.compose.material.icons.Icons
import androidx.compose.material.icons.filled.Add
import androidx.compose.material.icons.filled.Check
import androidx.compose.material.icons.filled.Search
import androidx.compose.material.icons.filled.Favorite
import androidx.compose.material.icons.filled.FavoriteBorder
import androidx.compose.material3.*
import androidx.compose.runtime.*
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
import androidx.compose.ui.text.style.TextOverflow
import androidx.compose.ui.unit.dp
import androidx.compose.ui.unit.sp
import coil.compose.rememberAsyncImagePainter
import com.example.data.model.MockData
import com.example.data.model.StickerPack
import com.example.ui.viewmodel.Screen
import com.example.ui.viewmodel.StickerViewModel

@OptIn(ExperimentalLayoutApi::class)
@Composable
fun ExploreScreen(viewModel: StickerViewModel) {
    val context = LocalContext.current
    val categories = viewModel.getCategories()
    val selectedCategory by viewModel.selectedCategory.collectAsState()
    val searchQuery by viewModel.searchQuery.collectAsState()

    val trendingPacks by viewModel.trendingPacks.collectAsState()
    val popularPacks by viewModel.popularPacks.collectAsState()
    val downloadedPacks by viewModel.downloadedPacks.collectAsState()
    val likedPacks by viewModel.likedPacks.collectAsState()

    // Handle Toast listener for WhatsApp export events
    LaunchedEffect(Unit) {
        viewModel.stickerExportSuccess.collect { message ->
            Toast.makeText(context, message, Toast.LENGTH_LONG).show()
        }
    }

    Box(
        modifier = Modifier
            .fillMaxSize()
            .background(MaterialTheme.colorScheme.background)
    ) {
        // Main Screen Scrollable content
        LazyColumn(
            modifier = Modifier
                .fillMaxSize()
                .padding(bottom = 80.dp), // Leaves room for Bottom Navigation
            contentPadding = PaddingValues(top = 16.dp, bottom = 16.dp)
        ) {
            // Header Title Bar
            item {
                Row(
                    modifier = Modifier
                        .fillMaxWidth()
                        .statusBarsPadding()
                        .padding(horizontal = 20.dp, vertical = 8.dp),
                    horizontalArrangement = Arrangement.SpaceBetween,
                    verticalAlignment = Alignment.CenterVertically
                ) {
                    Row(verticalAlignment = Alignment.CenterVertically) {
                        Surface(
                            modifier = Modifier.size(36.dp),
                            shape = CircleShape,
                            color = MaterialTheme.colorScheme.primary.copy(alpha = 0.1f)
                        ) {
                            Box(contentAlignment = Alignment.Center) {
                                Text(
                                    text = "✦",
                                    color = MaterialTheme.colorScheme.primary,
                                    fontSize = 20.sp,
                                    fontWeight = FontWeight.Bold
                                )
                            }
                        }
                        Spacer(modifier = Modifier.width(8.dp))
                        Text(
                            text = "Sticker Store",
                            fontWeight = FontWeight.ExtraBold,
                            fontSize = 24.sp,
                            color = MaterialTheme.colorScheme.primary,
                            modifier = Modifier.testTag("app_title")
                        )
                    }

                    Box(
                        modifier = Modifier
                            .size(40.dp)
                            .clip(CircleShape)
                            .background(MaterialTheme.colorScheme.surfaceVariant.copy(alpha = 0.4f))
                            .clickable { viewModel.searchPacks("Gatos") },
                        contentAlignment = Alignment.Center
                    ) {
                        Icon(
                            imageVector = Icons.Default.Search,
                            contentDescription = "Search icon button",
                            tint = MaterialTheme.colorScheme.onSurfaceVariant
                        )
                    }
                }
            }

            // High Fidelity Search Bar Input
            item {
                OutlinedTextField(
                    value = searchQuery,
                    onValueChange = { viewModel.searchPacks(it) },
                    placeholder = { Text("Pesquisar figurinhas, criadores ou packs...") },
                    leadingIcon = {
                        Icon(
                            imageVector = Icons.Default.Search,
                            contentDescription = "Search",
                            tint = MaterialTheme.colorScheme.onSurfaceVariant
                        )
                    },
                    shape = RoundedCornerShape(24.dp),
                    colors = OutlinedTextFieldDefaults.colors(
                        focusedContainerColor = MaterialTheme.colorScheme.surface,
                        unfocusedContainerColor = MaterialTheme.colorScheme.surface,
                        focusedBorderColor = MaterialTheme.colorScheme.primary,
                        unfocusedBorderColor = Color.Transparent
                    ),
                    modifier = Modifier
                        .fillMaxWidth()
                        .padding(horizontal = 20.dp, vertical = 8.dp)
                        .testTag("search_bar")
                )
            }

            // Categories list chips
            item {
                Column(modifier = Modifier.padding(vertical = 12.dp)) {
                    Text(
                        text = "Categorias",
                        fontWeight = FontWeight.Bold,
                        fontSize = 18.sp,
                        color = MaterialTheme.colorScheme.onSurface,
                        modifier = Modifier.padding(start = 20.dp, end = 20.dp, bottom = 8.dp)
                    )
                    LazyRow(
                        modifier = Modifier.fillMaxWidth(),
                        contentPadding = PaddingValues(horizontal = 20.dp),
                        horizontalArrangement = Arrangement.spacedBy(8.dp)
                    ) {
                        items(categories) { category ->
                            val isSelected = selectedCategory == category
                            val bgColor = if (isSelected) MaterialTheme.colorScheme.primary else MaterialTheme.colorScheme.surfaceVariant.copy(alpha = 0.5f)
                            val textColor = if (isSelected) MaterialTheme.colorScheme.onPrimary else MaterialTheme.colorScheme.onSurfaceVariant

                            Surface(
                                shape = RoundedCornerShape(100.dp),
                                color = bgColor,
                                modifier = Modifier
                                    .clickable { viewModel.setCategory(category) }
                                    .testTag("category_chip_${category.lowercase()}")
                            ) {
                                Text(
                                    text = category,
                                    color = textColor,
                                    fontWeight = FontWeight.SemiBold,
                                    fontSize = 13.sp,
                                    modifier = Modifier.padding(horizontal = 18.dp, vertical = 8.dp)
                                )
                            }
                        }
                    }
                }
            }

            // Trending horizontal sheet carousel
            item {
                Column(modifier = Modifier.padding(vertical = 12.dp)) {
                    Row(
                        modifier = Modifier
                            .fillMaxWidth()
                            .padding(start = 20.dp, end = 20.dp, bottom = 12.dp),
                        horizontalArrangement = Arrangement.SpaceBetween,
                        verticalAlignment = Alignment.Bottom
                    ) {
                        Text(
                            text = "Em Destaque",
                            fontWeight = FontWeight.Bold,
                            fontSize = 18.sp,
                            color = MaterialTheme.colorScheme.onSurface
                        )
                        Text(
                            text = "Ver Todos",
                            color = MaterialTheme.colorScheme.primary,
                            fontWeight = FontWeight.SemiBold,
                            fontSize = 12.sp,
                            modifier = Modifier.clickable { /* See All logic */ }
                        )
                    }

                    if (trendingPacks.isEmpty()) {
                        Box(
                            modifier = Modifier
                                .fillMaxWidth()
                                .height(220.dp)
                                .padding(horizontal = 20.dp),
                            contentAlignment = Alignment.Center
                        ) {
                            Text(
                                "Nenhum pacote em destaque encontrado.",
                                color = MaterialTheme.colorScheme.onSurfaceVariant
                            )
                        }
                    } else {
                        LazyRow(
                            modifier = Modifier.fillMaxWidth(),
                            contentPadding = PaddingValues(horizontal = 20.dp),
                            horizontalArrangement = Arrangement.spacedBy(16.dp)
                        ) {
                            items(trendingPacks) { pack ->
                                TrendingPackCard(
                                    pack = pack,
                                    viewModel = viewModel,
                                    isDownloaded = downloadedPacks.contains(pack.id),
                                    isLiked = likedPacks.contains(pack.id)
                                )
                            }
                        }
                    }
                }
            }

            // Popular packs list section
            item {
                Text(
                    text = "Pacotes Populares",
                    fontWeight = FontWeight.Bold,
                    fontSize = 18.sp,
                    color = MaterialTheme.colorScheme.onSurface,
                    modifier = Modifier.padding(start = 20.dp, end = 20.dp, top = 16.dp, bottom = 12.dp)
                )
            }

            if (popularPacks.isEmpty()) {
                item {
                    Box(
                        modifier = Modifier
                            .fillMaxWidth()
                            .height(100.dp),
                        contentAlignment = Alignment.Center
                    ) {
                        Text("Nenhum pacote popular encontrado.", color = MaterialTheme.colorScheme.onSurfaceVariant)
                    }
                }
            } else {
                items(popularPacks) { pack ->
                    PopularPackRow(
                        pack = pack,
                        viewModel = viewModel,
                        isDownloaded = downloadedPacks.contains(pack.id),
                        isLiked = likedPacks.contains(pack.id)
                    )
                }
            }
        }

        // Float Bottom Navigation Bar
        BottomNavigationBar(viewModel, currentActiveScreen = Screen.Explore)
    }
}

@OptIn(ExperimentalLayoutApi::class)
@Composable
fun TrendingPackCard(
    pack: StickerPack,
    viewModel: StickerViewModel,
    isDownloaded: Boolean,
    isLiked: Boolean
) {
    val context = LocalContext.current
    Card(
        modifier = Modifier
            .width(280.dp)
            .padding(bottom = 8.dp)
            .testTag("trending_card_${pack.id}"),
        shape = RoundedCornerShape(20.dp),
        colors = CardDefaults.cardColors(
            containerColor = MaterialTheme.colorScheme.surfaceVariant.copy(alpha = 0.25f)
        ),
        elevation = CardDefaults.cardElevation(2.dp)
    ) {
        Column(modifier = Modifier.padding(16.dp)) {
            // Card Title bar
            Row(
                modifier = Modifier.fillMaxWidth(),
                horizontalArrangement = Arrangement.SpaceBetween,
                verticalAlignment = Alignment.Top
            ) {
                Column(modifier = Modifier.weight(1f)) {
                    Row(
                        verticalAlignment = Alignment.CenterVertically
                    ) {
                        Text(
                            text = pack.name,
                            fontWeight = FontWeight.Bold,
                            fontSize = 18.sp,
                            color = MaterialTheme.colorScheme.onSurface,
                            maxLines = 1,
                            overflow = TextOverflow.Ellipsis,
                            modifier = Modifier.weight(1f, fill = false)
                        )
                        Spacer(modifier = Modifier.width(6.dp))
                        IconButton(
                            onClick = {
                                viewModel.toggleLikePack(pack.id)
                                val toastMsg = if (isLiked) "Removido dos favoritos" else "Adicionado aos favoritos (enviado para API!)"
                                Toast.makeText(context, toastMsg, Toast.LENGTH_SHORT).show()
                            },
                            modifier = Modifier.size(28.dp)
                        ) {
                            Icon(
                                imageVector = if (isLiked) Icons.Default.Favorite else Icons.Default.FavoriteBorder,
                                contentDescription = "Toggle favorite",
                                tint = if (isLiked) Color.Red else MaterialTheme.colorScheme.onSurfaceVariant,
                                modifier = Modifier.size(20.dp)
                            )
                        }
                    }
                    Text(
                        text = "por ${pack.creator}",
                        style = MaterialTheme.typography.labelSmall.copy(
                            color = MaterialTheme.colorScheme.onSurfaceVariant
                        )
                    )
                }

                if (pack.isPremium) {
                    Box(
                        modifier = Modifier
                            .clip(RoundedCornerShape(100.dp))
                            .background(Color(0xFF6CF8BB))
                            .padding(horizontal = 8.dp, vertical = 4.dp)
                    ) {
                        Text(
                            text = "PREMIUM",
                            color = Color(0xFF002113),
                            fontWeight = FontWeight.Black,
                            fontSize = 8.sp,
                            letterSpacing = 1.sp
                        )
                    }
                }
            }

            Spacer(modifier = Modifier.height(12.dp))

            // Sticker Grid Matrix (6 squares)
            Box(
                modifier = Modifier
                    .fillMaxWidth()
                    .aspectRatio(1.8f)
            ) {
                FlowRow(
                    modifier = Modifier.fillMaxWidth(),
                    maxItemsInEachRow = 3,
                    horizontalArrangement = Arrangement.spacedBy(8.dp),
                    verticalArrangement = Arrangement.spacedBy(8.dp)
                ) {
                    val previewStickers = (pack.stickers ?: emptyList()).take(6)
                    previewStickers.forEach { sticker ->
                        Card(
                            modifier = Modifier
                                .weight(1f)
                                .aspectRatio(1f),
                            shape = RoundedCornerShape(8.dp),
                            colors = CardDefaults.cardColors(containerColor = MaterialTheme.colorScheme.surface)
                        ) {
                            Image(
                                painter = rememberAsyncImagePainter(sticker.imageUrl),
                                contentDescription = sticker.contentDescription,
                                contentScale = ContentScale.Inside,
                                modifier = Modifier
                                    .fillMaxSize()
                                    .padding(4.dp)
                            )
                        }
                    }
                    if (previewStickers.size < 6) {
                        repeat(6 - previewStickers.size) {
                            Box(
                                modifier = Modifier
                                    .weight(1f)
                                    .aspectRatio(1f)
                                    .clip(RoundedCornerShape(8.dp))
                                    .background(MaterialTheme.colorScheme.surface.copy(alpha = 0.5f))
                            )
                        }
                    }
                }
            }

            Spacer(modifier = Modifier.height(16.dp))

            val waGreen = Color(0xFF006C49)
            Button(
                onClick = {
                    if (pack.isPremium && !viewModel.isPremiumMember.value) {
                        viewModel.navigateTo(Screen.Profile)
                        Toast.makeText(context, "Inscreva-se Premium para acessar pacotes exclusivos!", Toast.LENGTH_SHORT).show()
                    } else {
                        viewModel.downloadPackToDevice(pack.id)
                        viewModel.exportPackToWhatsApp(pack.name)
                    }
                },
                shape = RoundedCornerShape(100.dp),
                colors = ButtonDefaults.buttonColors(
                    containerColor = if (isDownloaded) Color.Gray else waGreen
                ),
                modifier = Modifier
                    .fillMaxWidth()
                    .height(44.dp)
                    .testTag("add_wa_btn_${pack.id}")
            ) {
                Row(
                    verticalAlignment = Alignment.CenterVertically,
                    horizontalArrangement = Arrangement.Center
                ) {
                    Icon(
                        imageVector = if (isDownloaded) Icons.Default.Check else Icons.Default.Add,
                        contentDescription = "Adicionar",
                        modifier = Modifier.size(16.dp),
                        tint = Color.White
                    )
                    Spacer(modifier = Modifier.width(6.dp))
                    Text(
                        text = if (isDownloaded) "Adicionado" else "Adicionar ao WhatsApp",
                        color = Color.White,
                        fontSize = 12.sp,
                        fontWeight = FontWeight.Bold
                    )
                }
            }
        }
    }
}

@Composable
fun PopularPackRow(
    pack: StickerPack,
    viewModel: StickerViewModel,
    isDownloaded: Boolean,
    isLiked: Boolean
) {
    val context = LocalContext.current
    Card(
        modifier = Modifier
            .fillMaxWidth()
            .padding(horizontal = 20.dp, vertical = 6.dp)
            .testTag("popular_row_${pack.id}"),
        shape = RoundedCornerShape(16.dp),
        colors = CardDefaults.cardColors(
            containerColor = MaterialTheme.colorScheme.surfaceVariant.copy(alpha = 0.15f)
        )
    ) {
        Row(
            modifier = Modifier
                .fillMaxWidth()
                .padding(12.dp),
            verticalAlignment = Alignment.CenterVertically,
            horizontalArrangement = Arrangement.SpaceBetween
        ) {
            Row(
                modifier = Modifier.weight(1f),
                verticalAlignment = Alignment.CenterVertically
            ) {
                Card(
                    modifier = Modifier.size(56.dp),
                    shape = RoundedCornerShape(10.dp),
                    colors = CardDefaults.cardColors(containerColor = MaterialTheme.colorScheme.surface)
                ) {
                    Image(
                        painter = rememberAsyncImagePainter((pack.stickers ?: emptyList()).firstOrNull()?.imageUrl),
                        contentDescription = pack.name,
                        contentScale = ContentScale.Crop,
                        modifier = Modifier.fillMaxSize()
                    )
                }

                Spacer(modifier = Modifier.width(16.dp))

                Column(modifier = Modifier.weight(1f)) {
                    Row(verticalAlignment = Alignment.CenterVertically) {
                        Text(
                            text = pack.name,
                            fontWeight = FontWeight.Bold,
                            fontSize = 16.sp,
                            color = MaterialTheme.colorScheme.onSurface,
                            maxLines = 1,
                            overflow = TextOverflow.Ellipsis
                        )
                        if (pack.isExclusive) {
                            Spacer(modifier = Modifier.width(6.dp))
                            Box(
                                modifier = Modifier
                                    .clip(RoundedCornerShape(4.dp))
                                    .background(Color(0xFFBC4800))
                                    .padding(horizontal = 6.dp, vertical = 2.dp)
                            ) {
                                Text(
                                    "EXCLUSIVE",
                                    color = Color.White,
                                    fontSize = 7.sp,
                                    fontWeight = FontWeight.ExtraBold
                                )
                            }
                        }
                    }
                    Text(
                        text = "${pack.creator} • ${pack.totalStickers} stickers",
                        style = MaterialTheme.typography.labelMedium.copy(
                            color = MaterialTheme.colorScheme.onSurfaceVariant
                        )
                    )
                }
            }

            Row(verticalAlignment = Alignment.CenterVertically) {
                IconButton(
                    onClick = {
                        viewModel.toggleLikePack(pack.id)
                        val toastMsg = if (isLiked) "Removido dos favoritos" else "Adicionado aos favoritos (enviado para API!)"
                        Toast.makeText(context, toastMsg, Toast.LENGTH_SHORT).show()
                    },
                    modifier = Modifier.size(40.dp)
                ) {
                    Icon(
                        imageVector = if (isLiked) Icons.Default.Favorite else Icons.Default.FavoriteBorder,
                        contentDescription = "Toggle favorite from popular pack list",
                        tint = if (isLiked) Color.Red else MaterialTheme.colorScheme.onSurfaceVariant
                    )
                }

                Spacer(modifier = Modifier.width(8.dp))

                IconButton(
                    onClick = {
                        viewModel.downloadPackToDevice(pack.id)
                        viewModel.exportPackToWhatsApp(pack.name)
                    },
                    modifier = Modifier
                        .size(40.dp)
                        .clip(CircleShape)
                        .background(
                            if (isDownloaded) MaterialTheme.colorScheme.secondary.copy(alpha = 0.15f)
                            else MaterialTheme.colorScheme.primary.copy(alpha = 0.1f)
                        )
                ) {
                    Icon(
                        imageVector = if (isDownloaded) Icons.Default.Check else Icons.Default.Add,
                        contentDescription = "Adicionar ao whatsapp",
                        tint = if (isDownloaded) MaterialTheme.colorScheme.secondary else MaterialTheme.colorScheme.primary
                    )
                }
            }
        }
    }
}

@Composable
fun BottomNavigationBar(viewModel: StickerViewModel, currentActiveScreen: Screen) {
    Box(
        modifier = Modifier.fillMaxSize(),
        contentAlignment = Alignment.BottomCenter
    ) {
        Surface(
            modifier = Modifier
                .fillMaxWidth()
                .navigationBarsPadding()
                .padding(horizontal = 12.dp, vertical = 12.dp)
                .height(72.dp)
                .testTag("global_bottom_navbar"),
            shape = RoundedCornerShape(24.dp),
            tonalElevation = 8.dp,
            shadowElevation = 16.dp,
            color = MaterialTheme.colorScheme.surface.copy(alpha = 0.90f)
        ) {
            Row(
                modifier = Modifier.fillMaxSize(),
                horizontalArrangement = Arrangement.SpaceAround,
                verticalAlignment = Alignment.CenterVertically
            ) {
                BottomNavItem(
                    title = "Explorar",
                    isSelected = currentActiveScreen == Screen.Explore,
                    iconSymbol = "explore",
                    onClick = { viewModel.navigateTo(Screen.Explore) }
                )

                BottomNavItem(
                    title = "Editor",
                    isSelected = currentActiveScreen == Screen.Editor,
                    iconSymbol = "add_circle",
                    onClick = { viewModel.navigateTo(Screen.Editor) }
                )

                BottomNavItem(
                    title = "Meus Packs",
                    isSelected = currentActiveScreen == Screen.MyPacks,
                    iconSymbol = "auto_awesome_motion",
                    onClick = { viewModel.navigateTo(Screen.MyPacks) }
                )

                BottomNavItem(
                    title = "Perfil",
                    isSelected = currentActiveScreen == Screen.Profile,
                    iconSymbol = "person",
                    onClick = { viewModel.navigateTo(Screen.Profile) }
                )
            }
        }
    }
}

@Composable
fun BottomNavItem(title: String, isSelected: Boolean, iconSymbol: String, onClick: () -> Unit) {
    val activeBgColor = if (isSelected) MaterialTheme.colorScheme.primary.copy(alpha = 0.15f) else Color.Transparent
    val activeColor = if (isSelected) MaterialTheme.colorScheme.primary else MaterialTheme.colorScheme.onSurfaceVariant

    Column(
        modifier = Modifier
            .clip(RoundedCornerShape(16.dp))
            .background(activeBgColor)
            .clickable(onClick = onClick)
            .padding(horizontal = 16.dp, vertical = 8.dp),
        horizontalAlignment = Alignment.CenterHorizontally,
        verticalArrangement = Arrangement.Center
    ) {
        Text(
            text = when (iconSymbol) {
                "explore" -> "🧭"
                "add_circle" -> "➕"
                "auto_awesome_motion" -> "📚"
                else -> "👤"
            },
            fontSize = 18.sp,
            textAlign = TextAlign.Center
        )
        Spacer(modifier = Modifier.height(2.dp))
        Text(
            text = title,
            color = activeColor,
            fontWeight = if (isSelected) FontWeight.ExtraBold else FontWeight.Medium,
            fontSize = 11.sp
        )
    }
}
