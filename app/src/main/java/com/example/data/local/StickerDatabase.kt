package com.example.data.local

import android.content.Context
import androidx.room.*
import kotlinx.coroutines.flow.Flow

@Entity(tableName = "local_packs")
data class LocalPackEntity(
    @PrimaryKey(autoGenerate = true) val localId: Int = 0,
    val name: String,
    val creator: String,
    val category: String,
    val stickersCsv: String, // Comma separated URLs/Paths
    val isPremium: Boolean = false,
    val isFavorite: Boolean = false
)

@Dao
interface StickerPackDao {
    @Query("SELECT * FROM local_packs ORDER BY localId DESC")
    fun getAllPacks(): Flow<List<LocalPackEntity>>

    @Query("SELECT * FROM local_packs WHERE isFavorite = 1")
    fun getFavoritePacks(): Flow<List<LocalPackEntity>>

    @Insert(onConflict = OnConflictStrategy.REPLACE)
    suspend fun insertPack(pack: LocalPackEntity)

    @Query("UPDATE local_packs SET isFavorite = :isFavorite WHERE localId = :id")
    suspend fun updateFavorite(id: Int, isFavorite: Boolean)

    @Query("DELETE FROM local_packs WHERE localId = :id")
    suspend fun deletePack(id: Int)
}

@Database(entities = [LocalPackEntity::class], version = 1, exportSchema = false)
abstract class StickerDatabase : RoomDatabase() {
    abstract fun stickerPackDao(): StickerPackDao

    companion object {
        @Volatile
        private var INSTANCE: StickerDatabase? = null

        fun getDatabase(context: Context): StickerDatabase {
            return INSTANCE ?: synchronized(this) {
                val instance = Room.databaseBuilder(
                    context.applicationContext,
                    StickerDatabase::class.java,
                    "sticker_database"
                )
                .fallbackToDestructiveMigration()
                .build()
                INSTANCE = instance
                instance
            }
        }
    }
}
