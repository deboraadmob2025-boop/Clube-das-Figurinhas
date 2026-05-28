package com.example.data.repo

import com.example.data.local.LocalPackEntity
import com.example.data.local.StickerPackDao
import com.example.data.model.MockData
import com.example.data.model.Sticker
import com.example.data.model.StickerPack
import kotlinx.coroutines.flow.Flow
import kotlinx.coroutines.flow.map

class StickerRepository(private val stickerPackDao: StickerPackDao) {

    // Mock servers lists representation
    fun getTrendingPacks(): List<StickerPack> = MockData.trendingPacks

    fun getPopularPacks(): List<StickerPack> = MockData.popularPacks

    fun getCategories(): List<String> = MockData.categories

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
}
