package com.example

import android.content.Context
import androidx.test.core.app.ApplicationProvider
import com.example.data.local.StickerDatabase
import com.example.data.repo.StickerRepository
import com.example.ui.viewmodel.StickerViewModel
import org.junit.Assert.assertEquals
import org.junit.Assert.assertNotNull
import org.junit.Test
import org.junit.runner.RunWith
import org.robolectric.RobolectricTestRunner
import org.robolectric.annotation.Config

@RunWith(RobolectricTestRunner::class)
@Config(sdk = [36])
class ExampleRobolectricTest {

  @Test
  fun `read string from context`() {
    val context = ApplicationProvider.getApplicationContext<Context>()
    val appName = context.getString(R.string.app_name)
    assertEquals("Sticker Store", appName)
  }

  @Test
  fun `initialize viewmodel and check categories`() = kotlinx.coroutines.runBlocking {
    val context = ApplicationProvider.getApplicationContext<Context>()
    val database = StickerDatabase.getDatabase(context)
    val dao = database.stickerPackDao()
    val repository = StickerRepository(dao)
    val viewModel = StickerViewModel(repository)
    assertNotNull(viewModel)
    assertNotNull(viewModel.categoriesState.value)
    
    // Test if remote call throws any Retrofit/Moshi configuration exceptions
    val categoriesRemote = repository.getCategoriesRemote()
    assertNotNull(categoriesRemote)
  }
}
