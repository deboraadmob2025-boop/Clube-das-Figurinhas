package com.example.data.remote

import com.example.data.model.Sticker
import com.example.data.model.StickerPack
import com.squareup.moshi.Moshi
import com.squareup.moshi.kotlin.reflect.KotlinJsonAdapterFactory
import okhttp3.MultipartBody
import okhttp3.OkHttpClient
import okhttp3.RequestBody
import okhttp3.logging.HttpLoggingInterceptor
import retrofit2.Retrofit
import retrofit2.converter.moshi.MoshiConverterFactory
import retrofit2.http.*
import java.util.concurrent.TimeUnit

// Safe Server Data Models
data class ApiResponse<T>(
    val success: Boolean,
    val status: Int,
    val message: String,
    val data: T?
)

data class AuthRequest(
    val email: String,
    val password: String
)

data class AuthResponseData(
    val token: String,
    val user: UserProfileResponse
)

data class UserProfileResponse(
    val id: Int,
    val name: String,
    val email: String,
    val avatar: String,
    val isPremium: Boolean
)

data class CategoryResponse(
    val id: Int,
    val name: String,
    val order_index: Int,
    val icon_emoji: String
)

data class AdPlacement(
    val adType: String,
    val unitIdTest: String,
    val unitIdProd: String,
    val isActive: Boolean
)

data class SystemConfig(
    val app_name: String,
    val logo_url: String,
    val primary_color: String,
    val policy_url: String
)

interface StickerApiService {

    @POST("api/login.php")
    suspend fun login(
        @Body request: AuthRequest
    ): ApiResponse<AuthResponseData>

    @GET("api/get_packs.php")
    suspend fun getPacks(
        @Query("category") category: String?,
        @Query("page") page: Int?,
        @Query("limit") limit: Int?
    ): ApiResponse<List<StickerPack>>

    @GET("api/get_categories.php")
    suspend fun getCategories(): ApiResponse<List<CategoryResponse>>

    @GET("api/trending.php")
    suspend fun getTrendingPacks(): ApiResponse<List<StickerPack>>

    @GET("api/search.php")
    suspend fun searchPacks(
        @Query("q") query: String
    ): ApiResponse<List<StickerPack>>

    @GET("api/ads.php")
    suspend fun getAdConfigs(): ApiResponse<List<AdPlacement>>

    @GET("api/config.php")
    suspend fun getAppConfig(): ApiResponse<Map<String, String>>

    @POST("api/favorites.php")
    suspend fun toggleFavorite(
        @Header("Authorization") token: String,
        @Body body: Map<String, Int>
    ): ApiResponse<Map<String, Boolean>>

    companion object {
        // Base Development URL falling back gracefully to a mockable web preview
        private const val BASE_URL = "https://mystickerstore.com/"

        fun create(): StickerApiService {
            val loggingInterceptor = HttpLoggingInterceptor().apply {
                level = HttpLoggingInterceptor.Level.BODY
            }

            val client = OkHttpClient.Builder()
                .connectTimeout(15, TimeUnit.SECONDS)
                .readTimeout(15, TimeUnit.SECONDS)
                .addInterceptor(loggingInterceptor)
                .build()

            val moshi = Moshi.Builder()
                .add(KotlinJsonAdapterFactory())
                .build()

            return Retrofit.Builder()
                .baseUrl(BASE_URL)
                .client(client)
                .addConverterFactory(MoshiConverterFactory.create(moshi))
                .build()
                .create(StickerApiService::class.java)
        }
    }
}
