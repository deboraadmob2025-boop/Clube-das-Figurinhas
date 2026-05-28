package com.example.ui.viewmodel

import android.os.Parcelable
import androidx.compose.runtime.mutableStateListOf
import androidx.compose.ui.geometry.Offset
import androidx.compose.ui.graphics.Color
import androidx.lifecycle.ViewModel
import androidx.lifecycle.ViewModelProvider
import androidx.lifecycle.viewModelScope
import com.example.data.model.MockData
import com.example.data.model.StickerPack
import com.example.data.remote.NotificationResponse
import com.example.data.repo.StickerRepository
import kotlinx.coroutines.delay
import kotlinx.coroutines.flow.*
import kotlinx.coroutines.launch

// Screen Enumeration for state-driven navigation
enum class Screen {
    Onboarding,
    Explore,
    Editor,
    Profile,
    MyPacks
}

// Drawing action data structures for high-fidelity canvas
data class LinePath(
    val points: List<Offset>,
    val color: Color = Color.Cyan,
    val strokeWidth: Float = 10f,
    val style: String = "Solid" // Solid, Soft Glow, Dashed
)

data class StickerText(
    val id: String,
    val text: String,
    val color: Color = Color.White,
    val size: Float = 24f,
    val position: Offset = Offset(150f, 150f)
)

data class StickerEmoji(
    val id: String,
    val symbol: String,
    val scale: Float = 1f,
    val position: Offset = Offset(100f, 100f)
)

class StickerViewModel(private val repository: StickerRepository) : ViewModel() {

    // --- Navigation & Flow State ---
    private val _currentScreen = MutableStateFlow(Screen.Onboarding)
    val currentScreen: StateFlow<Screen> = _currentScreen.asStateFlow()

    fun navigateTo(screen: Screen) {
        _currentScreen.value = screen
    }

    // --- Onboarding & Authenticity Mode ---
    private val _userLoggedIn = MutableStateFlow(false)
    val userLoggedIn: StateFlow<Boolean> = _userLoggedIn.asStateFlow()

    private val _userAvatar = MutableStateFlow(MockData.profileAvatarUrl)
    val userAvatar: StateFlow<String> = _userAvatar.asStateFlow()

    private val _userName = MutableStateFlow("Alex Rivera")
    val userName: StateFlow<String> = _userName.asStateFlow()

    fun completeOnboardingAndLogin() {
        _userLoggedIn.value = true
        _currentScreen.value = Screen.Explore
    }

    // --- Premium Signature Plan Mode ---
    private val _isPremiumMember = MutableStateFlow(false)
    val isPremiumMember: StateFlow<Boolean> = _isPremiumMember.asStateFlow()

    fun togglePremium() {
        _isPremiumMember.value = !_isPremiumMember.value
    }

    // --- Marketplace Explore Stats (Trending / Popular / Favorites) ---
    private val _selectedCategory = MutableStateFlow("todos")
    val selectedCategory: StateFlow<String> = _selectedCategory.asStateFlow()

    private val _searchQuery = MutableStateFlow("")
    val searchQuery: StateFlow<String> = _searchQuery.asStateFlow()

    private val _categoriesState = MutableStateFlow(repository.getCategories())
    val categoriesState: StateFlow<List<String>> = _categoriesState.asStateFlow()

    private val _notificationsState = MutableStateFlow<List<NotificationResponse>>(emptyList())
    val notificationsState: StateFlow<List<NotificationResponse>> = _notificationsState.asStateFlow()

    private val _remoteTrendingPacks = MutableStateFlow<List<StickerPack>>(emptyList())
    private val _remotePopularPacks = MutableStateFlow<List<StickerPack>>(emptyList())

    private val _apiLoading = MutableStateFlow(false)
    val apiLoading: StateFlow<Boolean> = _apiLoading.asStateFlow()

    init {
        loadApiData()
        fetchNotifications()
    }

    fun loadApiData() {
        viewModelScope.launch {
            _apiLoading.value = true
            try {
                val cats = repository.getCategoriesRemote()
                val updatedCats = mutableListOf("todos")
                updatedCats.addAll(cats.filter { it.lowercase() != "todos" })
                _categoriesState.value = updatedCats
            } catch (t: Throwable) {
                _categoriesState.value = listOf("todos") + repository.getCategories()
            }

            try {
                val trending = repository.getTrendingPacksRemote()
                _remoteTrendingPacks.value = trending
            } catch (t: Throwable) {
                _remoteTrendingPacks.value = repository.getTrendingPacks()
            }

            try {
                val popular = repository.getPopularPacksRemote()
                _remotePopularPacks.value = popular
            } catch (t: Throwable) {
                _remotePopularPacks.value = repository.getPopularPacks()
            }
            _apiLoading.value = false
        }
    }

    fun fetchNotifications() {
        viewModelScope.launch {
            try {
                _notificationsState.value = repository.getNotificationsRemote()
            } catch (e: Throwable) {
                _notificationsState.value = emptyList()
            }
        }
    }

    fun setCategory(category: String) {
        _selectedCategory.value = category
    }

    fun getCategories(): List<String> = categoriesState.value

    fun searchPacks(query: String) {
        _searchQuery.value = query
    }

    // Observes live remote trending packs filtered by categories & search in memory safely
    val trendingPacks: StateFlow<List<StickerPack>> = combine(
        _remoteTrendingPacks, _selectedCategory, _searchQuery
    ) { packs, category, query ->
        val source = if (packs.isEmpty()) repository.getTrendingPacks() else packs
        source.filter {
            val cat = (it.category ?: "General").lowercase()
            val nm = it.name ?: "Pacote Sem Nome"
            val cr = it.creator ?: "Criador Anônimo"
            val currentCategory = category ?: "todos"
            val matchesCategory = currentCategory.lowercase() == "todos" || currentCategory.lowercase() == "memes" || cat == currentCategory.lowercase()
            val matchesQuery = query.isEmpty() || nm.contains(query, ignoreCase = true) || cr.contains(query, ignoreCase = true)
            matchesCategory && matchesQuery
        }
    }.stateIn(viewModelScope, SharingStarted.WhileSubscribed(5000), repository.getTrendingPacks())

    // Observes live popular packs filtered in memory safely
    val popularPacks: StateFlow<List<StickerPack>> = combine(
        _remotePopularPacks, _searchQuery
    ) { packs, query ->
        val source = if (packs.isEmpty()) repository.getPopularPacks() else packs
        if (query.isEmpty()) {
            source
        } else {
            source.filter {
                val nm = it.name ?: ""
                val cr = it.creator ?: ""
                nm.contains(query, ignoreCase = true) || cr.contains(query, ignoreCase = true)
            }
        }
    }.stateIn(viewModelScope, SharingStarted.WhileSubscribed(5000), repository.getPopularPacks())

    // Exposes custom created packs stored inside Room SQLite
    val userCustomPacks: StateFlow<List<StickerPack>> = repository.localPacks
        .stateIn(viewModelScope, SharingStarted.WhileSubscribed(5000), emptyList())

    // --- Custom Action: Add Pack / Downloads / Likes ---
    private val _likedPacks = MutableStateFlow<Set<String>>(emptySet())
    val likedPacks: StateFlow<Set<String>> = _likedPacks.asStateFlow()

    private val _downloadedPacks = MutableStateFlow<Set<String>>(emptySet())
    val downloadedPacks: StateFlow<Set<String>> = _downloadedPacks.asStateFlow()

    fun toggleLikePack(packId: String) {
        viewModelScope.launch {
            val current = _likedPacks.value.toMutableSet()
            val isFav = !current.contains(packId)
            
            if (current.contains(packId)) {
                current.remove(packId)
            } else {
                current.add(packId)
            }
            _likedPacks.value = current

            // Demonstrates sending favorites live to API REST server (como enviar favoritos)
            try {
                val packIntId = packId.filter { it.isDigit() }.toIntOrNull() ?: 1
                repository.toggleRemoteFavorite("session_mock_token_key_12345", packIntId)
            } catch (t: Throwable) {
                // Fail silently or log
            }
        }
    }

    // Background remove simulator, downloading packs, etc.
    fun downloadPackToDevice(packId: String) {
        viewModelScope.launch {
            val current = _downloadedPacks.value.toMutableSet()
            current.add(packId)
            _downloadedPacks.value = current
        }
    }

    // --- sticker creations & room writing ---
    fun createAndSaveCustomPack(name: String, creator: String, category: String, stickers: List<String>) {
        viewModelScope.launch {
            repository.createPack(name, creator, category, stickers)
            _currentScreen.value = Screen.Explore
        }
    }

    // --- EDITING CANVAS ACTIVE ELEMENTS ---
    var drawingPaths = mutableStateListOf<LinePath>()
    var addedTexts = mutableStateListOf<StickerText>()
    var addedEmojis = mutableStateListOf<StickerEmoji>()

    // Color list for brush selection
    val brushColors = listOf(Color.Cyan, Color.Red, Color.Yellow, Color.Green, Color.White, Color.Magenta)
    
    private val _selectedBrushColor = MutableStateFlow(Color.Cyan)
    val selectedBrushColor: StateFlow<Color> = _selectedBrushColor.asStateFlow()

    private val _brushWidth = MutableStateFlow(24f)
    val brushWidth: StateFlow<Float> = _brushWidth.asStateFlow()

    private val _brushStyle = MutableStateFlow("Solid") // Solid, Soft Glow, Dashed
    val brushStyle: StateFlow<String> = _brushStyle.asStateFlow()

    private val _activeEditorImage = MutableStateFlow(MockData.defaultEditorStickerUrl)
    val activeEditorImage: StateFlow<String> = _activeEditorImage.asStateFlow()

    private val _aiBgRemovalProcessing = MutableStateFlow(false)
    val aiBgRemovalProcessing: StateFlow<Boolean> = _aiBgRemovalProcessing.asStateFlow()

    private val _isBgRemoved = MutableStateFlow(false)
    val isBgRemoved: StateFlow<Boolean> = _isBgRemoved.asStateFlow()

    fun selectBrushColor(color: Color) {
        _selectedBrushColor.value = color
    }

    fun setBrushWidth(width: Float) {
        _brushWidth.value = width
    }

    fun setBrushStyle(style: String) {
        _brushStyle.value = style
    }

    // AI Background removal execution simulation with professional progress bar feedback
    fun removeBackgroundWithIA() {
        viewModelScope.launch {
            _aiBgRemovalProcessing.value = true
            delay(2500) // Simulates intense AI model segmenting foreground
            _isBgRemoved.value = true
            _aiBgRemovalProcessing.value = false
        }
    }

    fun addTextToSticker(text: String) {
        if (text.isNotBlank()) {
            addedTexts.add(
                StickerText(
                    id = "txt_${System.currentTimeMillis()}",
                    text = text,
                    color = _selectedBrushColor.value,
                    size = _brushWidth.value
                )
            )
        }
    }

    fun addEmojiToSticker(emoji: String) {
        addedEmojis.add(
            StickerEmoji(
                id = "emo_${System.currentTimeMillis()}",
                symbol = emoji,
                scale = _brushWidth.value / 24f
            )
        )
    }

    fun clearCanvas() {
        drawingPaths.clear()
        addedTexts.clear()
        addedEmojis.clear()
        _isBgRemoved.value = false
    }

    // Exporting action simulation
    private val _stickerExportSuccess = MutableSharedFlow<String>()
    val stickerExportSuccess: SharedFlow<String> = _stickerExportSuccess.asSharedFlow()

    fun exportPackToWhatsApp(packName: String) {
        viewModelScope.launch {
            _stickerExportSuccess.emit("O pacote \"$packName\" foi exportado para o WhatsApp com sucesso!")
        }
    }
}

// Custom viewmodel factory since we pass custom database repository initialization
class StickerViewModelFactory(private val repository: StickerRepository) : ViewModelProvider.Factory {
    override fun <T : ViewModel> create(modelClass: Class<T>): T {
        if (modelClass.isAssignableFrom(StickerViewModel::class.java)) {
            @Suppress("UNCHECKED_CAST")
            return StickerViewModel(repository) as T
        }
        throw IllegalArgumentException("Unknown ViewModel class")
    }
}
