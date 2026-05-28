package com.example.data.repo

import com.example.data.local.LocalPackEntity
import com.example.data.local.StickerPackDao
import com.example.data.model.MockData
import com.example.data.model.Sticker
import com.example.data.model.StickerPack
import com.example.data.remote.NotificationResponse
import com.example.data.remote.StickerApiService
import kotlinx.coroutines.flow.Flow
import kotlinx.coroutines.flow.map

class StickerRepository(
    private val stickerPackDao: StickerPackDao,
    private val apiService: StickerApiService = StickerApiService.create()
) {

    // Fetch live categories from network, fallback to offline defaults on failure
    suspend fun getCategoriesRemote(): List<String> {
        return try {
            val response = apiService.getCategories()
            if (response.success && response.data != null) {
                response.data.map { it.name }
            } else {
                MockData.categories
            }
        } catch (e: Throwable) {
            MockData.categories
        }
    }

    fun getCategories(): List<String> = MockData.categories

    // Fetch and sync trending packs from live API, with offline fallback
    suspend fun getTrendingPacksRemote(): List<StickerPack> {
        return try {
            val response = apiService.getTrendingPacks()
            if (response.success && response.data != null) {
                response.data
            } else {
                MockData.trendingPacks
            }
        } catch (e: Throwable) {
            MockData.trendingPacks
        }
    }

    fun getTrendingPacks(): List<StickerPack> = MockData.trendingPacks

    // Fetch packs by search query from remote server, with fallback
    suspend fun searchPacksRemote(query: String): List<StickerPack> {
        if (query.isBlank()) return getPopularPacksRemote()
        return try {
            val response = apiService.searchPacks(query)
            if (response.success && response.data != null) {
                response.data
            } else {
                MockData.trendingPacks.filter {
                    it.name.contains(query, ignoreCase = true) || it.creator.contains(query, ignoreCase = true)
                }
            }
        } catch (e: Throwable) {
            MockData.trendingPacks.filter {
                it.name.contains(query, ignoreCase = true) || it.creator.contains(query, ignoreCase = true)
            }
        }
    }

    // Fetch popular packs from remote API, falling back to local list on failure
    suspend fun getPopularPacksRemote(): List<StickerPack> {
        return try {
            val response = apiService.getPacks(category = null, page = 1, limit = 20)
            if (response.success && response.data != null) {
                response.data
            } else {
                MockData.popularPacks
            }
        } catch (e: Throwable) {
            MockData.popularPacks
        }
    }

    fun getPopularPacks(): List<StickerPack> = MockData.popularPacks

    // Fetch Custom user packs
    val localPacks: Flow<List<StickerPack>> = stickerPackDao.getAllPacks().map { entities ->
        entities.map { entity ->
            val stickersList = entity.stickersCsv.split(",").filter { it.isNotBlank() }.mapIndexed { index, url ->
                Sticker("local_${entity.localId}_$index", url, "Sticker designed by client")
            }
            StickerPack(
                id = "local_${entity.localId}",
                name = entity.name,
                creator = entity.creator,
                stickers = stickersList,
                category = entity.category,
                isPremium = entity.isPremium,
                downloads = "N/A"
            )
        }
    }

    // Retrieve custom items marked as favorite
    val favoritePacks: Flow<List<StickerPack>> = stickerPackDao.getFavoritePacks().map { entities ->
        entities.map { entity ->
            val stickersList = entity.stickersCsv.split(",").filter { it.isNotBlank() }.mapIndexed { index, url ->
                Sticker("local_fav_${entity.localId}_$index", url, "Favorite Sticker")
            }
            StickerPack(
                id = "local_${entity.localId}",
                name = entity.name,
                creator = entity.creator,
                stickers = stickersList,
                category = entity.category,
                isPremium = entity.isPremium,
                downloads = "Saved"
            )
        }
    }

    suspend fun createPack(name: String, creator: String, category: String, stickerUrls: List<String>) {
        val csv = stickerUrls.joinToString(",")
        val entity = LocalPackEntity(
            name = name,
            creator = creator,
            category = category,
            stickersCsv = csv,
            isPremium = false
        )
        stickerPackDao.insertPack(entity)
    }

    suspend fun deletePack(localId: Int) {
        stickerPackDao.deletePack(localId)
    }

    suspend fun toggleFavorite(localId: Int, isFav: Boolean) {
        stickerPackDao.updateFavorite(localId, isFav)
    }

    suspend fun getNotificationsRemote(): List<NotificationResponse> {
        return try {
            val response = apiService.getNotifications()
            if (response.success && response.data != null) {
                response.data
            } else {
                emptyList()
            }
        } catch (e: Throwable) {
            emptyList()
        }
    }

    suspend fun getStickersRemote(packId: Int): List<Sticker> {
        return try {
            val response = apiService.getStickers(packId)
            if (response.success && response.data != null) {
                response.data
            } else {
                emptyList()
            }
        } catch (e: Throwable) {
            emptyList()
        }
    }

    suspend fun toggleRemoteFavorite(token: String, packId: Int): Boolean {
        return try {
            val response = apiService.toggleFavorite("Bearer $token", mapOf("pack_id" to packId))
            if (response.success && response.data != null) {
                response.data.is_favorite
            } else {
                false
            }
        } catch (e: Throwable) {
            false
        }
    }
}
