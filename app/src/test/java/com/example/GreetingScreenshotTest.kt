package com.example

import android.content.Context
import androidx.compose.ui.test.junit4.createComposeRule
import androidx.compose.ui.test.onRoot
import androidx.test.core.app.ApplicationProvider
import com.example.data.local.StickerDatabase
import com.example.data.repo.StickerRepository
import com.example.ui.screens.OnboardingScreen
import com.example.ui.screens.ExploreScreen
import com.example.ui.theme.MyApplicationTheme
import com.example.ui.viewmodel.StickerViewModel
import com.github.takahirom.roborazzi.RobolectricDeviceQualifiers
import com.github.takahirom.roborazzi.captureRoboImage
import org.junit.Rule
import org.junit.Test
import org.junit.runner.RunWith
import org.robolectric.RobolectricTestRunner
import org.robolectric.annotation.Config
import org.robolectric.annotation.GraphicsMode

@RunWith(RobolectricTestRunner::class)
@GraphicsMode(GraphicsMode.Mode.NATIVE)
@Config(qualifiers = RobolectricDeviceQualifiers.Pixel8, sdk = [36])
class GreetingScreenshotTest {

  @get:Rule val composeTestRule = createComposeRule()

  @Test
  fun test_onboarding_screen_composition() {
    val context = ApplicationProvider.getApplicationContext<Context>()
    val database = StickerDatabase.getDatabase(context)
    val dao = database.stickerPackDao()
    val repository = StickerRepository(dao)
    val viewModel = StickerViewModel(repository)

    composeTestRule.setContent {
      MyApplicationTheme {
        OnboardingScreen(viewModel = viewModel)
      }
    }

    composeTestRule.onRoot().captureRoboImage(filePath = "src/test/screenshots/onboarding.png")
  }

  @Test
  fun test_explore_screen_composition() {
    val context = ApplicationProvider.getApplicationContext<Context>()
    val database = StickerDatabase.getDatabase(context)
    val dao = database.stickerPackDao()
    val repository = StickerRepository(dao)
    val viewModel = StickerViewModel(repository)

    composeTestRule.setContent {
      MyApplicationTheme {
        ExploreScreen(viewModel = viewModel)
      }
    }

    composeTestRule.onRoot().captureRoboImage(filePath = "src/test/screenshots/explore.png")
  }
}
