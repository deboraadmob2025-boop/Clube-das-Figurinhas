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
import androidx.compose.material.icons.filled.Menu
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

    var isSearchExpanded by remember { mutableStateOf(false) }

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
        // 1. Scrollable Main Feed
        LazyColumn(
            modifier = Modifier
                .fillMaxSize()
                .padding(bottom = 80.dp), // Leaves room for Bottom Navigation
            contentPadding = PaddingValues(top = 76.dp, bottom = 16.dp)
        ) {
            // Slider Carousel Hero Banner
            item {
                HeroBannerSlider()
            }

            // Categories list chips custom cards
            item {
                Column(modifier = Modifier.padding(vertical = 12.dp)) {
                    Text(
                        text = "Categorias populares",
                        fontWeight = FontWeight.Bold,
                        fontSize = 18.sp,
                        color = MaterialTheme.colorScheme.onSurface,
                        modifier = Modifier.padding(start = 20.dp, end = 20.dp, bottom = 10.dp)
                    )
                    CategoryCardList(
                        categories = categories,
                        selectedCategory = selectedCategory,
                        onCategoryClick = { viewModel.setCategory(it) }
                    )
                }
            }

            // Search input field togggable
            if (isSearchExpanded || searchQuery.isNotEmpty()) {
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
                            unfocusedBorderColor = MaterialTheme.colorScheme.outlineVariant
                        ),
                        modifier = Modifier
                            .fillMaxWidth()
                            .padding(horizontal = 20.dp, vertical = 6.dp)
                            .testTag("search_bar")
                    )
                }
            }

            // Section label
            item {
                Text(
                    text = "Pacotes de Figurinhas",
                    fontWeight = FontWeight.Bold,
                    fontSize = 18.sp,
                    color = MaterialTheme.colorScheme.onSurface,
                    modifier = Modifier.padding(start = 20.dp, end = 20.dp, top = 8.dp, bottom = 8.dp)
                )
            }

            // Sticker packs listings feed
            if (trendingPacks.isEmpty() && popularPacks.isEmpty()) {
                item {
                    Box(
                        modifier = Modifier
                            .fillMaxWidth()
                            .padding(vertical = 48.dp, horizontal = 24.dp),
                        contentAlignment = Alignment.Center
                    ) {
                        Text(
                            text = "Nenhum pacote encontrado para esta categoria.",
                            textAlign = TextAlign.Center,
                            color = MaterialTheme.colorScheme.onSurfaceVariant,
                            style = MaterialTheme.typography.bodyMedium
                        )
                    }
                }
            } else {
                // Main feed displays categories sorted elegantly
                val combinedPacks = (trendingPacks + popularPacks).distinctBy { it.id }
                items(combinedPacks) { pack ->
                    StickerPackFeedCard(
                        pack = pack,
                        viewModel = viewModel,
                        isDownloaded = downloadedPacks.contains(pack.id),
                        isLiked = likedPacks.contains(pack.id)
                    )
                }
            }
        }

        // 2. Fixed Custom Top App Bar (matching the user's reference image structure)
        Column(
            modifier = Modifier
                .fillMaxWidth()
                .background(MaterialTheme.colorScheme.surface.copy(alpha = 0.95f))
                .statusBarsPadding()
        ) {
            Row(
                modifier = Modifier
                    .fillMaxWidth()
                    .height(64.dp)
                    .padding(horizontal = 14.dp),
                verticalAlignment = Alignment.CenterVertically,
                horizontalArrangement = Arrangement.SpaceBetween
            ) {
                // Left Hamburger Menu
                IconButton(onClick = {
                    Toast.makeText(context, "Menu lateral em breve!", Toast.LENGTH_SHORT).show()
                }) {
                    Icon(
                        imageVector = Icons.Default.Menu,
                        contentDescription = "Menu lateral",
                        tint = MaterialTheme.colorScheme.onSurface,
                        modifier = Modifier.size(26.dp)
                    )
                }

                // Title
                Text(
                    text = "WAStickerApps - Stickers",
                    fontWeight = FontWeight.ExtraBold,
                    fontSize = 20.sp,
                    color = MaterialTheme.colorScheme.onSurface,
                    modifier = Modifier
                        .weight(1f)
                        .padding(start = 12.dp)
                        .testTag("app_title")
                )

                // Search Toggle Icon button
                IconButton(onClick = {
                    isSearchExpanded = !isSearchExpanded
                    if (!isSearchExpanded) {
                        viewModel.searchPacks("")
                    }
                }) {
                    Icon(
                        imageVector = Icons.Default.Search,
                        contentDescription = "Buscar figurinhas",
                        tint = MaterialTheme.colorScheme.onSurface,
                        modifier = Modifier.size(24.dp)
                    )
                }

                // Crown Button
                IconButton(onClick = {
                    viewModel.navigateTo(Screen.Profile)
                    Toast.makeText(context, "Explore os benefícios Premium!", Toast.LENGTH_SHORT).show()
                }) {
                    Text(text = "👑", fontSize = 22.sp)
                }
            }

            // Separator
            HorizontalDivider(
                color = MaterialTheme.colorScheme.outlineVariant.copy(alpha = 0.35f),
                thickness = 1.dp
            )
        }

        // 3. Floating Action Button (Green "+")
        Box(
            modifier = Modifier
                .fillMaxSize()
                .padding(bottom = 96.dp, end = 20.dp), // floats elegantly above bottom bar
            contentAlignment = Alignment.BottomEnd
        ) {
            FloatingActionButton(
                onClick = { viewModel.navigateTo(Screen.Editor) },
                containerColor = Color(0xFF25D366), // WhatsApp Green Color!
                contentColor = Color.White,
                shape = CircleShape,
                modifier = Modifier
                    .size(56.dp)
                    .testTag("fab_add_custom_pack")
            ) {
                Icon(
                    imageVector = Icons.Default.Add,
                    contentDescription = "Criar figurinha customizada",
                    modifier = Modifier.size(28.dp)
                )
            }
        }

        // 4. Floating Bottom Navigation Bar (5 elegant options)
        BottomNavigationBar(viewModel, currentActiveScreen = Screen.Explore)
    }
}

@Composable
fun HeroBannerSlider() {
    val banners = listOf(
        Pair("SUPER HERO STICKERS", "https://lh3.googleusercontent.com/aida-public/AB6AXuCkAzBBb1__SVPkIuHkfcDl4zyruX49g19VhCK4bZZ2_PDf3BUYpk7ksR1Z3BMAcTeArsiyBSbv2UrKnhaNPrxoui8QA8yiMABXnNn-6dnErCYv_i58RPdsqCQexECcnFTi-EYWAk_pK4sRDdl2RH9cAnrPTZvP9ezALqXMZuHJYmKxxk4pXj6-XP6W6yLgO-2VZhrjP3BIAeI-5UKhMT0F5jrniqqupYChtd8EI4Gb5mFRtiQ3Qi3VgXgAfNAeKWYcTDMUs2SRTDY"),
        Pair("ANIME SHONEN PACKS", "https://lh3.googleusercontent.com/aida-public/AB6AXuCbQKu4xi2rSFCUmECsOd0jlZrmd0WpriDRIgllI2siRHpYl-P3QzfnNEgS27qh5aWwna4SOOLfuC3rlJQE6rAhrh7Cc3ocLu5fQRAf4k4hmVHWX9EKWMtYt2gAi1EQ1FwPca9BwaygkQlTYE7l9Uk98EucLQHHekHNnIqDNDW6Z7wi6k14TfnfSTJUfoagrqF69CXurRPfjq0kx6f15kPKK6PObH6K1IUUgbgQspKGXRcqlS9pBpJ6tR8XPdd45C5qtAMHc2h_h2o"),
        Pair("MEMES ENGRAÇADOS 2026", "https://lh3.googleusercontent.com/aida-public/AB6AXuA9hmtX4zI7nQcF7Vmyk5uc1sQt8HSCsYpZkIIUYgHf5kSawHSk9wjJXIJewCdSLlYv3SWK_cFsmbv4sBOIEXJq-8QM8kohJ74Zju0HRy_0rkQXI9znqk1F78jkXpgQo8v6qeyYETOSaq4GausT79Dlmz8wpTiySAzV3U4XQTSApyqlvAIXeDz2iNDee1ynz_aaySEyJQLNmjtDWEbRCifxyirmMWfJGT7FuOBmMr-oUMwZITL5DTazl0D2DZQkDd4245WgiRRcJ30")
    )
    var activeIndex by remember { mutableStateOf(0) }

    // Automatic slide looping loop
    LaunchedEffect(Unit) {
        while (true) {
            kotlinx.coroutines.delay(4500)
            activeIndex = (activeIndex + 1) % banners.size
        }
    }

    Card(
        modifier = Modifier
            .fillMaxWidth()
            .height(180.dp)
            .padding(horizontal = 20.dp, vertical = 8.dp),
        shape = RoundedCornerShape(20.dp),
        elevation = CardDefaults.cardElevation(defaultElevation = 3.dp)
    ) {
        Box(modifier = Modifier.fillMaxSize()) {
            Image(
                painter = rememberAsyncImagePainter(banners[activeIndex].second),
                contentDescription = banners[activeIndex].first,
                contentScale = ContentScale.Crop,
                modifier = Modifier.fillMaxSize()
            )
            // Visual vignette gradient overlay
            Box(
                modifier = Modifier
                    .fillMaxSize()
                    .background(
                        Brush.verticalGradient(
                            colors = listOf(Color.Transparent, Color.Black.copy(alpha = 0.70f))
                        )
                    )
            )

            // Dynamic card caption
            Column(
                modifier = Modifier
                    .align(Alignment.BottomStart)
                    .padding(16.dp)
            ) {
                Text(
                    text = banners[activeIndex].first,
                    color = Color.White,
                    fontWeight = FontWeight.ExtraBold,
                    fontSize = 20.sp,
                    letterSpacing = 0.5.sp
                )
                Text(
                    text = "Lançamento Exclusivo do Dia",
                    color = Color.White.copy(alpha = 0.8f),
                    fontSize = 11.sp,
                    fontWeight = FontWeight.Medium
                )
            }

            // Indicator Dots to match the bottom-right design of the user's screenshot!
            Row(
                modifier = Modifier
                    .align(Alignment.BottomEnd)
                    .padding(16.dp),
                horizontalArrangement = Arrangement.spacedBy(4.dp),
                verticalAlignment = Alignment.CenterVertically
            ) {
                banners.forEachIndexed { i, _ ->
                    Box(
                        modifier = Modifier
                            .size(if (i == activeIndex) 16.dp else 6.dp, 6.dp)
                            .clip(CircleShape)
                            .background(if (i == activeIndex) Color.White else Color.White.copy(alpha = 0.45f))
                    )
                }
            }
        }
    }
}

@Composable
fun CategoryCardList(
    categories: List<String>,
    selectedCategory: String,
    onCategoryClick: (String) -> Unit
) {
    LazyRow(
        modifier = Modifier.fillMaxWidth(),
        contentPadding = PaddingValues(horizontal = 20.dp, vertical = 4.dp),
        horizontalArrangement = Arrangement.spacedBy(10.dp)
    ) {
        items(categories) { cat ->
            val isSelected = selectedCategory.lowercase() == cat.lowercase()

            // Custom design mappings for each category to look exactly like the TV/Funny Cards
            val (emoji, sub, cardColor) = when (cat.lowercase()) {
                "todos" -> Triple("📦", "Todos packs", Color(0xFF2CAAA6))
                "memes" -> Triple("😆", "19 packs", Color(0xFF5D71F2))
                "love" -> Triple("❤️", "14 packs", Color(0xFFE93B57))
                "anime" -> Triple("🦊", "22 packs", Color(0xFF7A3BE9))
                "funny" -> Triple("📺", "24 packs", Color(0xFF2AB04A))
                "animals" -> Triple("🐶", "15 packs", Color(0xFFFF9A00))
                "gaming" -> Triple("🎮", "30 packs", Color(0xFFE28413))
                else -> Triple("✨", "12 packs", Color(0xFF8D99AE))
            }

            Card(
                modifier = Modifier
                    .width(156.dp)
                    .height(76.dp)
                    .clickable { onCategoryClick(cat) }
                    .testTag("category_card_${cat.lowercase()}"),
                shape = RoundedCornerShape(18.dp),
                colors = CardDefaults.cardColors(
                    containerColor = if (isSelected) cardColor else cardColor.copy(alpha = 0.12f)
                ),
                border = if (!isSelected) androidx.compose.foundation.BorderStroke(1.2.dp, cardColor.copy(alpha = 0.3f)) else null
            ) {
                Row(
                    modifier = Modifier
                        .fillMaxSize()
                        .padding(10.dp),
                    verticalAlignment = Alignment.CenterVertically
                ) {
                    // Ellipse Icon wrapper bubble matched dynamically
                    Box(
                        modifier = Modifier
                            .size(40.dp)
                            .clip(CircleShape)
                            .background(if (isSelected) Color.White.copy(alpha = 0.25f) else cardColor.copy(alpha = 0.2f)),
                        contentAlignment = Alignment.Center
                    ) {
                        Text(text = emoji, fontSize = 20.sp)
                    }

                    Spacer(modifier = Modifier.width(10.dp))

                    // Detail Labels
                    Column(verticalArrangement = Arrangement.Center) {
                        Text(
                            text = cat.replaceFirstChar { it.uppercase() },
                            color = if (isSelected) Color.White else MaterialTheme.colorScheme.onSurface,
                            fontWeight = FontWeight.Bold,
                            fontSize = 14.sp,
                            maxLines = 1,
                            overflow = TextOverflow.Ellipsis
                        )
                        Spacer(modifier = Modifier.height(2.dp))
                        Text(
                            text = sub,
                            color = if (isSelected) Color.White.copy(alpha = 0.85f) else MaterialTheme.colorScheme.onSurfaceVariant,
                            fontSize = 11.sp,
                            fontWeight = FontWeight.Medium
                        )
                    }
                }
            }
        }
    }
}

@Composable
fun StickerPackFeedCard(
    pack: StickerPack,
    viewModel: StickerViewModel,
    isDownloaded: Boolean,
    isLiked: Boolean
) {
    val context = LocalContext.current

    Card(
        modifier = Modifier
            .fillMaxWidth()
            .padding(horizontal = 20.dp, vertical = 8.dp)
            .testTag("feed_card_${pack.id}")
            .clickable {
                if (pack.isPremium && !viewModel.isPremiumMember.value) {
                    viewModel.navigateTo(Screen.Profile)
                    Toast.makeText(context, "Inscreva-se Premium para acessar pacotes de criadores!", Toast.LENGTH_SHORT).show()
                } else {
                    viewModel.downloadPackToDevice(pack.id)
                    viewModel.exportPackToWhatsApp(pack.name)
                }
            },
        shape = RoundedCornerShape(20.dp),
        colors = CardDefaults.cardColors(
            containerColor = MaterialTheme.colorScheme.surface
        ),
        elevation = CardDefaults.cardElevation(defaultElevation = 2.dp),
        border = androidx.compose.foundation.BorderStroke(1.dp, MaterialTheme.colorScheme.outlineVariant.copy(alpha = 0.40f))
    ) {
        Column(modifier = Modifier.padding(16.dp)) {
            // 1. Top Section (Avatar, Pack metadata, heart icon)
            Row(
                modifier = Modifier.fillMaxWidth(),
                verticalAlignment = Alignment.CenterVertically
            ) {
                // Sticker pack layout master avatar box
                Card(
                    modifier = Modifier.size(54.dp),
                    shape = RoundedCornerShape(14.dp),
                    colors = CardDefaults.cardColors(
                        containerColor = MaterialTheme.colorScheme.surfaceVariant.copy(alpha = 0.3f)
                    ),
                    border = androidx.compose.foundation.BorderStroke(1.dp, MaterialTheme.colorScheme.outlineVariant.copy(alpha = 0.45f))
                ) {
                    Box(modifier = Modifier.fillMaxSize(), contentAlignment = Alignment.Center) {
                        Image(
                            painter = rememberAsyncImagePainter((pack.stickers ?: emptyList()).firstOrNull()?.imageUrl),
                            contentDescription = "Pack logo",
                            contentScale = ContentScale.Crop,
                            modifier = Modifier.fillMaxSize()
                        )
                    }
                }

                Spacer(modifier = Modifier.width(12.dp))

                // Title and branding subtitle
                Column(modifier = Modifier.weight(1f)) {
                    Row(verticalAlignment = Alignment.CenterVertically) {
                        Text(
                            text = pack.name,
                            fontWeight = FontWeight.ExtraBold,
                            fontSize = 16.sp,
                            color = MaterialTheme.colorScheme.onSurface,
                            maxLines = 1,
                            overflow = TextOverflow.Ellipsis
                        )
                        if (pack.isPremium) {
                            Spacer(modifier = Modifier.width(6.dp))
                            Box(
                                modifier = Modifier
                                    .clip(RoundedCornerShape(4.dp))
                                    .background(Color(0xFFE28413))
                                    .padding(horizontal = 5.dp, vertical = 2.dp)
                            ) {
                                Text(
                                    "PRO",
                                    color = Color.White,
                                    fontSize = 8.sp,
                                    fontWeight = FontWeight.Black
                                )
                            }
                        }
                    }
                    Spacer(modifier = Modifier.height(2.dp))
                    Text(
                        text = "WhatsApp • Pack Inteligente",
                        style = MaterialTheme.typography.labelMedium.copy(
                            color = MaterialTheme.colorScheme.onSurfaceVariant.copy(alpha = 0.65f),
                            fontWeight = FontWeight.Bold
                        )
                    )
                }

                // Heart like button in Red
                IconButton(
                    onClick = {
                        viewModel.toggleLikePack(pack.id)
                        val toastMsg = if (isLiked) "Removido dos favoritos" else "Adicionado aos favoritos"
                        Toast.makeText(context, toastMsg, Toast.LENGTH_SHORT).show()
                    },
                    modifier = Modifier.size(40.dp)
                ) {
                    Icon(
                        imageVector = if (isLiked) Icons.Default.Favorite else Icons.Default.FavoriteBorder,
                        contentDescription = "Toggle favorite from main feed list",
                        tint = if (isLiked) Color(0xFFE93B57) else MaterialTheme.colorScheme.onSurfaceVariant.copy(alpha = 0.4f),
                        modifier = Modifier.size(26.dp)
                    )
                }
            }

            Spacer(modifier = Modifier.height(14.dp))

            // 2. Middle Section (Sticker Previews inside bubble frames side-by-side)
            Row(
                modifier = Modifier.fillMaxWidth(),
                horizontalArrangement = Arrangement.spacedBy(8.dp),
                verticalAlignment = Alignment.CenterVertically
            ) {
                val previewStickers = (pack.stickers ?: emptyList()).take(5)
                previewStickers.forEach { sticker ->
                    Card(
                        modifier = Modifier
                            .weight(1f)
                            .aspectRatio(1f),
                        shape = RoundedCornerShape(12.dp),
                        colors = CardDefaults.cardColors(
                            containerColor = MaterialTheme.colorScheme.surfaceVariant.copy(alpha = 0.15f)
                        ),
                        border = androidx.compose.foundation.BorderStroke(1.dp, MaterialTheme.colorScheme.outlineVariant.copy(alpha = 0.35f))
                    ) {
                        Box(modifier = Modifier.fillMaxSize(), contentAlignment = Alignment.Center) {
                            Image(
                                painter = rememberAsyncImagePainter(sticker.imageUrl),
                                contentDescription = sticker.contentDescription,
                                contentScale = ContentScale.Fit,
                                modifier = Modifier
                                    .fillMaxSize()
                                    .padding(6.dp)
                            )
                        }
                    }
                }

                // Empty fillers if pack has fewer than 5 stickers
                if (previewStickers.size < 5) {
                    repeat(5 - previewStickers.size) {
                        Box(
                            modifier = Modifier
                                .weight(1f)
                                .aspectRatio(1f)
                                .clip(RoundedCornerShape(12.dp))
                                .background(MaterialTheme.colorScheme.surfaceVariant.copy(alpha = 0.05f))
                        )
                    }
                }
            }

            Spacer(modifier = Modifier.height(14.dp))

            // Thin border separating the bottom analytics row
            HorizontalDivider(
                color = MaterialTheme.colorScheme.outlineVariant.copy(alpha = 0.3f),
                thickness = 1.dp
            )

            Spacer(modifier = Modifier.height(10.dp))

            // 3. Bottom Row Section (Author, Download count, File size, added label)
            Row(
                modifier = Modifier.fillMaxWidth(),
                horizontalArrangement = Arrangement.SpaceBetween,
                verticalAlignment = Alignment.CenterVertically
            ) {
                // Author Layout
                Row(
                    modifier = Modifier.weight(1f, fill = false),
                    verticalAlignment = Alignment.CenterVertically
                ) {
                    Box(
                        modifier = Modifier
                            .size(20.dp)
                            .clip(CircleShape)
                            .background(MaterialTheme.colorScheme.primary.copy(alpha = 0.12f)),
                        contentAlignment = Alignment.Center
                    ) {
                        Text(text = "✍️", fontSize = 10.sp)
                    }
                    Spacer(modifier = Modifier.width(6.dp))
                    Text(
                        text = pack.creator,
                        color = MaterialTheme.colorScheme.onSurfaceVariant,
                        fontSize = 11.sp,
                        fontWeight = FontWeight.Bold,
                        maxLines = 1,
                        overflow = TextOverflow.Ellipsis
                    )
                }

                // Download metrics
                Row(verticalAlignment = Alignment.CenterVertically) {
                    Text(text = "📥", fontSize = 11.sp)
                    Spacer(modifier = Modifier.width(4.dp))
                    Text(
                        text = pack.downloads,
                        color = MaterialTheme.colorScheme.onSurfaceVariant,
                        fontSize = 11.sp,
                        fontWeight = FontWeight.SemiBold
                    )
                }

                // File weights
                Row(verticalAlignment = Alignment.CenterVertically) {
                    Text(text = "💾", fontSize = 11.sp)
                    Spacer(modifier = Modifier.width(4.dp))
                    val fileSize = (pack.stickers?.size ?: 3) * 12 + 18
                    Text(
                        text = "${fileSize} KB",
                        color = MaterialTheme.colorScheme.onSurfaceVariant,
                        fontSize = 11.sp,
                        fontWeight = FontWeight.SemiBold
                    )
                }

                // Dynamic age/time tags
                Row(verticalAlignment = Alignment.CenterVertically) {
                    Text(text = "🕒", fontSize = 11.sp)
                    Spacer(modifier = Modifier.width(4.dp))
                    Text(
                        text = "Recente",
                        color = MaterialTheme.colorScheme.onSurfaceVariant,
                        fontSize = 11.sp,
                        fontWeight = FontWeight.SemiBold
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
            color = MaterialTheme.colorScheme.surface.copy(alpha = 0.95f)
        ) {
            Row(
                modifier = Modifier.fillMaxSize(),
                horizontalArrangement = Arrangement.SpaceAround,
                verticalAlignment = Alignment.CenterVertically
            ) {
                // Latest Tab -> Shows main Explore screen
                BottomNavItem(
                    title = "Latest",
                    isSelected = currentActiveScreen == Screen.Explore && viewModel.selectedCategory.value.lowercase() == "todos",
                    iconSymbol = "latest",
                    onClick = {
                        viewModel.setCategory("todos")
                        viewModel.navigateTo(Screen.Explore)
                    }
                )

                // Trending Tab -> Filters Explore screen to Memes dynamically
                BottomNavItem(
                    title = "Trending",
                    isSelected = currentActiveScreen == Screen.Explore && viewModel.selectedCategory.value.lowercase() == "memes",
                    iconSymbol = "trending",
                    onClick = {
                        viewModel.setCategory("Memes")
                        viewModel.navigateTo(Screen.Explore)
                    }
                )

                // Editor Tab -> Navigates to sticker custom studio editor
                BottomNavItem(
                    title = "Criar",
                    isSelected = currentActiveScreen == Screen.Editor,
                    iconSymbol = "editor",
                    onClick = { viewModel.navigateTo(Screen.Editor) }
                )

                // My Packs Collection folder -> Downloads & creations
                BottomNavItem(
                    title = "Coleções",
                    isSelected = currentActiveScreen == Screen.MyPacks,
                    iconSymbol = "mypacks",
                    onClick = { viewModel.navigateTo(Screen.MyPacks) }
                )

                // Premium / User Profile Tab
                BottomNavItem(
                    title = "Premium",
                    isSelected = currentActiveScreen == Screen.Profile,
                    iconSymbol = "premium",
                    onClick = { viewModel.navigateTo(Screen.Profile) }
                )
            }
        }
    }
}

@Composable
fun BottomNavItem(
    title: String,
    isSelected: Boolean,
    iconSymbol: String,
    onClick: () -> Unit
) {
    // Elegant light peach / pink active capsule matching the reference picture style
    val activeBgColor = if (isSelected) Color(0xFFFFD4D4) else Color.Transparent
    val activeTextColor = if (isSelected) Color(0xFFC0392B) else MaterialTheme.colorScheme.onSurfaceVariant

    Column(
        modifier = Modifier
            .clip(RoundedCornerShape(16.dp))
            .background(activeBgColor)
            .clickable(onClick = onClick)
            .padding(horizontal = 14.dp, vertical = 6.dp),
        horizontalAlignment = Alignment.CenterHorizontally,
        verticalArrangement = Arrangement.Center
    ) {
        Text(
            text = when (iconSymbol) {
                "latest" -> "🏪"
                "trending" -> "⚡"
                "editor" -> "➕"
                "mypacks" -> "📚"
                else -> "👑"
            },
            fontSize = 18.sp,
            textAlign = TextAlign.Center
        )
        Spacer(modifier = Modifier.height(2.dp))
        Text(
            text = title,
            color = activeTextColor,
            fontWeight = if (isSelected) FontWeight.ExtraBold else FontWeight.Medium,
            fontSize = 11.sp
        )
    }
}
