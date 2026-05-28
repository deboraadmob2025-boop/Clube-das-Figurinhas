package com.example.ui.screens

import android.widget.Toast
import androidx.compose.animation.*
import androidx.compose.foundation.Canvas
import androidx.compose.foundation.background
import androidx.compose.foundation.border
import androidx.compose.foundation.clickable
import androidx.compose.foundation.gestures.detectDragGestures
import androidx.compose.foundation.layout.*
import androidx.compose.foundation.lazy.LazyRow
import androidx.compose.foundation.lazy.items
import androidx.compose.foundation.shape.CircleShape
import androidx.compose.foundation.shape.RoundedCornerShape
import androidx.compose.material.icons.Icons
import androidx.compose.material.icons.filled.ArrowBack
import androidx.compose.material3.*
import androidx.compose.runtime.*
import androidx.compose.ui.Alignment
import androidx.compose.ui.Modifier
import androidx.compose.ui.draw.clip
import androidx.compose.ui.geometry.Offset
import androidx.compose.ui.graphics.Color
import androidx.compose.ui.graphics.Path
import androidx.compose.ui.graphics.StrokeCap
import androidx.compose.ui.graphics.StrokeJoin
import androidx.compose.ui.graphics.drawscope.Stroke
import androidx.compose.ui.graphics.graphicsLayer
import androidx.compose.ui.input.pointer.pointerInput
import androidx.compose.ui.layout.ContentScale
import androidx.compose.ui.platform.LocalContext
import androidx.compose.ui.platform.testTag
import androidx.compose.ui.text.font.FontWeight
import androidx.compose.ui.text.style.TextAlign
import androidx.compose.ui.unit.dp
import androidx.compose.ui.unit.sp
import androidx.compose.ui.window.Dialog
import coil.compose.AsyncImage
import com.example.ui.viewmodel.LinePath
import com.example.ui.viewmodel.Screen
import com.example.ui.viewmodel.StickerViewModel

@OptIn(ExperimentalLayoutApi::class)
@Composable
fun EditorScreen(viewModel: StickerViewModel) {
    val context = LocalContext.current
    val activeImage by viewModel.activeEditorImage.collectAsState()
    val isBgRemoved by viewModel.isBgRemoved.collectAsState()
    val aiProcessing by viewModel.aiBgRemovalProcessing.collectAsState()

    val selectedColor by viewModel.selectedBrushColor.collectAsState()
    val brushWidth by viewModel.brushWidth.collectAsState()
    val brushStyle by viewModel.brushStyle.collectAsState()

    var showTextDialog by remember { mutableStateOf(false) }
    var showEmojiDialog by remember { mutableStateOf(false) }
    var textInput by remember { mutableStateOf("") }

    var currentDragPoints = remember { mutableStateListOf<Offset>() }

    Box(
        modifier = Modifier
            .fillMaxSize()
            .background(Color(0xFF111218)) // Clean dark professional UI
    ) {
        // App Header Navigation Bar
        Row(
            modifier = Modifier
                .fillMaxWidth()
                .statusBarsPadding()
                .padding(horizontal = 20.dp, vertical = 12.dp),
            horizontalArrangement = Arrangement.SpaceBetween,
            verticalAlignment = Alignment.CenterVertically
        ) {
            IconButton(
                onClick = { viewModel.navigateTo(Screen.Explore) },
                modifier = Modifier
                    .clip(CircleShape)
                    .background(Color.White.copy(alpha = 0.1f))
            ) {
                Icon(
                    imageVector = Icons.Default.ArrowBack,
                    contentDescription = "Voltar",
                    tint = Color.White
                )
            }

            Text(
                text = "Editor Criativo",
                color = Color.White,
                fontWeight = FontWeight.Bold,
                fontSize = 18.sp
            )

            Button(
                onClick = {
                    viewModel.createAndSaveCustomPack(
                        name = "Figurinhas do Alex",
                        creator = "Alex Rivera",
                        category = "Personalizado",
                        stickers = listOf(activeImage)
                    )
                    Toast.makeText(context, "Figurinha criada com sucesso no 'Meus Packs'!", Toast.LENGTH_SHORT).show()
                },
                shape = RoundedCornerShape(100.dp),
                colors = ButtonDefaults.buttonColors(containerColor = MaterialTheme.colorScheme.primaryContainer),
                modifier = Modifier.testTag("save_sticker_btn")
            ) {
                Text("Salvar", fontWeight = FontWeight.Bold, color = Color.White)
            }
        }

        // Active Interactive Canvas Layout with Drag detection
        Box(
            modifier = Modifier
                .fillMaxSize()
                .padding(horizontal = 32.dp, vertical = 100.dp)
                .clip(RoundedCornerShape(32.dp))
                .border(2.dp, Color.White.copy(alpha = 0.08f), RoundedCornerShape(32.dp))
                .pointerInput(Unit) {
                    detectDragGestures(
                        onDragStart = { offset ->
                            currentDragPoints.clear()
                            currentDragPoints.add(offset)
                        },
                        onDrag = { change, dragAmount ->
                            change.consume()
                            currentDragPoints.add(change.position)
                        },
                        onDragEnd = {
                            if (currentDragPoints.isNotEmpty()) {
                                viewModel.drawingPaths.add(
                                    LinePath(
                                        points = currentDragPoints.toList(),
                                        color = selectedColor,
                                        strokeWidth = brushWidth,
                                        style = brushStyle
                                    )
                                )
                                currentDragPoints.clear()
                            }
                        }
                    )
                },
            contentAlignment = Alignment.Center
        ) {
            // Draw standard sticker-maker grid checkerboard background inside canvas
            Canvas(modifier = Modifier.fillMaxSize()) {
                val step = 40f
                for (x in 0 until (size.width / step).toInt()) {
                    for (y in 0 until (size.height / step).toInt()) {
                        if ((x + y) % 2 == 0) {
                            drawRect(
                                color = Color(0xFF1E2129),
                                topLeft = Offset(x * step, y * step),
                                size = androidx.compose.ui.geometry.Size(step, step)
                            )
                        } else {
                            drawRect(
                                color = Color(0xFF12141C),
                                topLeft = Offset(x * step, y * step),
                                size = androidx.compose.ui.geometry.Size(step, step)
                            )
                        }
                    }
                }
            }

            // Foreground image cutout rendering
            AsyncImage(
                model = if (isBgRemoved) "https://lh3.googleusercontent.com/aida-public/AB6AXuCkAzBBb1__SVPkIuHkfcDl4zyruX49g19VhCK4bZZ2_PDf3BUYpk7ksR1Z3BMAcTeArsiyBSbv2UrKnhaNPrxoui8QA8yiMABXnNn-6dnErCYv_i58RPdsqCQexECcnFTi-EYWAk_pK4sRDdl2RH9cAnrPTZvP9ezALqXMZuHJYmKxxk4pXj6-XP6W6yLgO-2VZhrjP3BIAeI-5UKhMT0F5jrniqqupYChtd8EI4Gb5mFRtiQ3Qi3VgXgAfNAeKWYcTDMUs2SRTDY" else activeImage,
                contentDescription = "Active Image to Edit",
                contentScale = ContentScale.Fit,
                modifier = Modifier
                    .fillMaxSize(0.75f)
                    .graphicsLayer(alpha = if (aiProcessing) 0.5f else 1.0f)
            )

            // Drawing Canvas layer overlays which paint offsets in real-time
            Canvas(modifier = Modifier.fillMaxSize()) {
                // Paint previously saved lines
                viewModel.drawingPaths.forEach { line ->
                    if (line.points.size > 1) {
                        val path = Path().apply {
                            moveTo(line.points.first().x, line.points.first().y)
                            line.points.drop(1).forEach { l -> lineTo(l.x, l.y) }
                        }
                        drawPath(
                            path = path,
                            color = line.color,
                            style = Stroke(
                                width = line.strokeWidth,
                                cap = StrokeCap.Round,
                                join = StrokeJoin.Round
                            )
                        )
                    }
                }

                // Paint currently active line under progress
                if (currentDragPoints.size > 1) {
                    val path = Path().apply {
                        moveTo(currentDragPoints.first().x, currentDragPoints.first().y)
                        currentDragPoints.drop(1).forEach { l -> lineTo(l.x, l.y) }
                    }
                    drawPath(
                        path = path,
                        color = selectedColor,
                        style = Stroke(
                            width = brushWidth,
                            cap = StrokeCap.Round,
                            join = StrokeJoin.Round
                        )
                    )
                }
            }

            // Floating Custom Overlapped Text Labels Box
            viewModel.addedTexts.forEach { textItem ->
                Box(
                    modifier = Modifier
                        .fillMaxSize()
                        .padding(16.dp),
                    contentAlignment = Alignment.Center
                ) {
                    Text(
                        text = textItem.text,
                        color = textItem.color,
                        fontWeight = FontWeight.Black,
                        fontSize = textItem.size.sp,
                        textAlign = TextAlign.Center
                    )
                }
            }

            // Floating Custom Overlapped Emoji Boxes
            viewModel.addedEmojis.forEach { emoji ->
                Box(
                    modifier = Modifier
                        .fillMaxSize()
                        .padding(16.dp),
                    contentAlignment = Alignment.Center
                ) {
                    Text(
                        text = emoji.symbol,
                        fontSize = (44 * emoji.scale).sp
                    )
                }
            }

            // Processing AI loader progress bar
            AnimatedVisibility(
                visible = aiProcessing,
                enter = fadeIn(),
                exit = fadeOut()
            ) {
                Box(
                    modifier = Modifier
                        .fillMaxSize()
                        .background(Color.Black.copy(alpha = 0.5f)),
                    contentAlignment = Alignment.Center
                ) {
                    Column(horizontalAlignment = Alignment.CenterHorizontally) {
                        CircularProgressIndicator(color = MaterialTheme.colorScheme.primaryContainer)
                        Spacer(modifier = Modifier.height(16.dp))
                        Text(
                            "Removendo fundo com IA...",
                            color = Color.White,
                            fontWeight = FontWeight.Bold
                        )
                    }
                }
            }
        }

        // Float Tool side-cluster toolbar (Remove BG, Crop, Text, Emoji, Brush)
        Column(
            modifier = Modifier
                .align(Alignment.CenterEnd)
                .padding(end = 20.dp),
            verticalArrangement = Arrangement.spacedBy(12.dp),
            horizontalAlignment = Alignment.CenterHorizontally
        ) {
            // IA background removal magic eraser tool
            FloatingToolBtn("✨", onClick = { viewModel.removeBackgroundWithIA() })

            // Manual image cropping selector triggers background toggle
            FloatingToolBtn("✂️", onClick = {
                viewModel.clearCanvas()
                Toast.makeText(context, "Tela de figurinha redefinida!", Toast.LENGTH_SHORT).show()
            })

            // Overlay custom textual string dialog trigger
            FloatingToolBtn("✍️", onClick = { showTextDialog = true })

            // Overlay emoji sticker catalog list triggers emoji insert dialog
            FloatingToolBtn("🎭", onClick = { showEmojiDialog = true })
        }

        // Bottom Settings Bar layout framework containing Brush stroke parameters
        Surface(
            modifier = Modifier
                .fillMaxWidth()
                .align(Alignment.BottomCenter)
                .navigationBarsPadding()
                .padding(horizontal = 12.dp, vertical = 12.dp)
                .height(130.dp),
            shape = RoundedCornerShape(24.dp),
            color = Color(0xFF1E2129).copy(alpha = 0.95f),
            tonalElevation = 4.dp
        ) {
            Column(modifier = Modifier.padding(12.dp)) {
                // Slide description info
                Row(
                    modifier = Modifier.fillMaxWidth(),
                    horizontalArrangement = Arrangement.SpaceBetween,
                    verticalAlignment = Alignment.CenterVertically
                ) {
                    Text(
                        text = "AJUSTES DO PINCEL",
                        color = MaterialTheme.colorScheme.primary,
                        fontSize = 11.sp,
                        fontWeight = FontWeight.ExtraBold,
                        letterSpacing = 1.sp
                    )
                    Text(
                        text = "${brushWidth.toInt()}px",
                        color = Color.White,
                        fontSize = 12.sp,
                        fontWeight = FontWeight.Bold
                    )
                }

                Spacer(modifier = Modifier.height(4.dp))

                // Brush width slider
                Slider(
                    value = brushWidth,
                    onValueChange = { viewModel.setBrushWidth(it) },
                    valueRange = 5f..80f,
                    colors = SliderDefaults.colors(
                        thumbColor = MaterialTheme.colorScheme.primaryContainer,
                        activeTrackColor = MaterialTheme.colorScheme.primaryContainer
                    ),
                    modifier = Modifier.height(24.dp)
                )

                // Style Chips option selectors (Solid, Soft Glow, Dashed)
                val styles = listOf("Solid", "Soft Glow", "Dashed")
                LazyRow(
                    modifier = Modifier.fillMaxWidth(),
                    horizontalArrangement = Arrangement.spacedBy(8.dp)
                ) {
                    items(styles) { style ->
                        val isSelected = brushStyle == style
                        val bg = if (isSelected) MaterialTheme.colorScheme.primaryContainer else Color.White.copy(alpha = 0.05f)
                        val txtColor = if (isSelected) Color.White else Color.White.copy(alpha = 0.6f)

                        Box(
                            modifier = Modifier
                                .clip(RoundedCornerShape(100.dp))
                                .background(bg)
                                .clickable { viewModel.setBrushStyle(style) }
                                .padding(horizontal = 16.dp, vertical = 6.dp)
                        ) {
                            Text(style, color = txtColor, fontSize = 11.sp, fontWeight = FontWeight.Bold)
                        }
                    }
                }
            }
        }
    }

    // Modal dialog to type overlapping text
    if (showTextDialog) {
        Dialog(onDismissRequest = { showTextDialog = false }) {
            Surface(
                modifier = Modifier
                    .fillMaxWidth()
                    .padding(16.dp),
                shape = RoundedCornerShape(24.dp),
                color = Color(0xFF1E2129)
            ) {
                Column(
                    modifier = Modifier.padding(16.dp),
                    horizontalAlignment = Alignment.CenterHorizontally
                ) {
                    Text("Adicionar Texto", color = Color.White, fontWeight = FontWeight.Bold, fontSize = 16.sp)
                    Spacer(modifier = Modifier.height(12.dp))
                    OutlinedTextField(
                        value = textInput,
                        onValueChange = { textInput = it },
                        placeholder = { Text("Digite sua piada/frase...") },
                        colors = OutlinedTextFieldDefaults.colors(focusedTextColor = Color.White, unfocusedTextColor = Color.White),
                        modifier = Modifier.fillMaxWidth()
                    )
                    Spacer(modifier = Modifier.height(16.dp))
                    Row(
                        modifier = Modifier.fillMaxWidth(),
                        horizontalArrangement = Arrangement.SpaceAround
                    ) {
                        TextButton(onClick = { showTextDialog = false }) {
                            Text("Cancelar", color = Color.LightGray)
                        }
                        Button(
                            onClick = {
                                viewModel.addTextToSticker(textInput)
                                textInput = ""
                                showTextDialog = false
                            },
                            colors = ButtonDefaults.buttonColors(containerColor = MaterialTheme.colorScheme.primaryContainer)
                        ) {
                            Text("Adicionar", color = Color.White)
                        }
                    }
                }
            }
        }
    }

    // Modal dialog to choose stickers/emojis overlay
    if (showEmojiDialog) {
        Dialog(onDismissRequest = { showEmojiDialog = false }) {
            Surface(
                modifier = Modifier
                    .fillMaxWidth()
                    .padding(16.dp),
                shape = RoundedCornerShape(24.dp),
                color = Color(0xFF1E2129)
            ) {
                Column(
                    modifier = Modifier.padding(16.dp),
                    horizontalAlignment = Alignment.CenterHorizontally
                ) {
                    Text("Adicionar Emoji", color = Color.White, fontWeight = FontWeight.Bold, fontSize = 16.sp)
                    Spacer(modifier = Modifier.height(12.dp))
                    val emojis = listOf("🔥", "😂", "😎", "🐱", "🚀", "🍕", "💖", "👀")
                    FlowRow(
                        modifier = Modifier.fillMaxWidth(),
                        maxItemsInEachRow = 4,
                        horizontalArrangement = Arrangement.spacedBy(16.dp),
                        verticalArrangement = Arrangement.spacedBy(16.dp)
                    ) {
                        emojis.forEach { emoji ->
                            Text(
                                emoji,
                                fontSize = 32.sp,
                                modifier = Modifier
                                    .clickable {
                                        viewModel.addEmojiToSticker(emoji)
                                        showEmojiDialog = false
                                    }
                                    .padding(8.dp)
                            )
                        }
                    }
                }
            }
        }
    }
}

@Composable
fun FloatingToolBtn(labelEmoji: String, onClick: () -> Unit) {
    Box(
        modifier = Modifier
            .size(48.dp)
            .clip(CircleShape)
            .background(Color(0xFF1E2129).copy(alpha = 0.90f))
            .border(1.dp, Color.White.copy(alpha = 0.08f), CircleShape)
            .clickable(onClick = onClick),
        contentAlignment = Alignment.Center
    ) {
        Text(labelEmoji, fontSize = 20.sp, textAlign = TextAlign.Center)
    }
}
