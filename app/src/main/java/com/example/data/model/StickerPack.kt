package com.example.data.model

data class Sticker(
    val id: String,
    val imageUrl: String,
    val contentDescription: String
)

data class StickerPack(
    val id: String,
    val name: String,
    val creator: String,
    val stickers: List<Sticker>,
    val category: String,
    val isPremium: Boolean = false,
    val isExclusive: Boolean = false,
    val totalStickers: Int = stickers.size,
    val downloads: String = "1.2k",
    val likes: Int = 482
)

object MockData {
    val categories = listOf("Memes", "Love", "Anime", "Funny", "Animals", "Gaming")

    // Realistic Web Sample Stickers from our design mockup matching standard tech platforms
    val cyberGatosStickers = listOf(
        Sticker("cg1", "https://lh3.googleusercontent.com/aida-public/AB6AXuCtkSf5UeSaJUridQTq5N4vmoVsP0gexrgiYTdP0qRlbEs30ew908UhCNEi9m4LssAhXwVOg57u1e_ez-5g3TCOLjGUqA_-27lK6b3gjBo5fwN-xvC_bKUNHrOkf3ISxAXnFmmx8mWdAdYoIk0HajgPSXMuRMLYN6Jh-eRtFtqy4r1QWUPvDtru9uh3AoAcbANQjuMITfhzZ5Rkxqa6gWxcRXhsv9GA-oJYENfKNAiEofeImH6pZ-f27B90qgHjsVEMaAFiBSJb5Tg", "Futuristic blue neon cat"),
        Sticker("cg2", "https://lh3.googleusercontent.com/aida-public/AB6AXuA3wfyyh4fYujNiui8ykRW7sThV2EscYZZpuXEFUI3NbIeojw_q5XIjhtJdEBbvtm3fXWgkX4UrUX1db3BoaTMLIPTva7bXJyYDpMYL7t9XPotyDJ73vhvtYxh4TTsCtHbjFwB0iHI9Z3iMQfYO3eDIFPJpWzNE6RIj54lAQanMU5km61D3nyzx6c88sHI7KXZXyTjQAcnhhZ4NQTlnqxYzhetVswujz_XcYvqXC3brpw24bx52M5QPqVMlWJq2E6CwiI5cCH3-hjI", "Robot cat with digital hearts"),
        Sticker("cg3", "https://lh3.googleusercontent.com/aida-public/AB6AXuDD7tu1cGmRMHMaEKcw5RrpUj3EwSleP1nyuecTI2gqx-s1i6gw1p1zcCj9s0zdJhSWEbAzRK-CHR86zaV24La9Vhsh_5q1TEJtMYmJ_gNxQ1ZGzHnOG8hHd_PWTY04YjQCRu6MhXmGgfn7KNMmzhOdlf_7LDWx8rQa8r80ig7z5cCADJZ5iVwMqj180g7fqDuRSI2ZH4Xlyno0L1Y_NjPBMmm9Dn7V3ZYJcr1R2Tt5I6-rk5NbEk90_ZvI_JbVwypgKrZkQFcFJcw", "Tearing laughing futuristic cat"),
        Sticker("cg4", "https://lh3.googleusercontent.com/aida-public/AB6AXuBokIKEgJNan6aLiMhMivjzxqTCbzwfZxZuvU9W39YnKmc109J5ouRvUFQNEaBkQzdJ8YuABxktHPTuxnO4z2Icp1i9o3zre8wN_4FoOmE3DtUg42uF14W76Yf2vcv4lswPAjoTvBaCDMb-yzSOOCIQ9P8myGRrwobNobytvGAQlpYh111clC24jW5W3oyrFXOZQxEbchokxY_XdwU08VVj5foLf16gZPQGHbOAbAF7CSChNgAqYhsfnOKYg1YF8PJUVbEChLqlCV8", "Futuristic emerald cat smirking"),
        Sticker("cg5", "https://lh3.googleusercontent.com/aida-public/AB6AXuCmTLiCXK3nk56qOLKbOQAtHnDRikSSpmfi6Qf6jraKljqm2_QSYo7CD0v2pnwaDDrguCEfpsEWd5XYAQz4tngCPku-LF1HQGSH_XjQZuF6AZxwieHal2ja_uqxpFeVWWmlrE4cDM2dWXM4wQT_D2Hl4DyspHLLtdRB_UykxImu0EOxiEvTRyDhEgcg-uq4w56prb2-wXBlKn7kolxBU6oErd6uBUKJ_mhf_9PyB8KXZ6EL0T-FlKb4WElSiIyZUip9kg4OxjT-CCw", "Kawaii white cute cat waving"),
        Sticker("cg6", "https://lh3.googleusercontent.com/aida-public/AB6AXuBfExOVvS__0n92l4WpAFKSAPyLRmcIh6zfxievdhR9mXOLw0G9L0tYF7EGQLsjY3J4aTxXeFf9yj4PPk_QbennT4HIQIH2LSJIzy1y6Si9n8UJvVCBCvgPdnEdodv9YJBvQEijPQq_iTFY6Q8TTN_liz0krzyh9t4MJ0wFsH5_CjIGfyL0O-qfN7ezrM3Ckg4NeStp86dVCMy0rVMO-CJpouHmINsodby_kKGz1uRB-QSmJ8Hezx1b130kuQ3NiHs8svRhyosgDUI", "Wide-eyed green robot cat"),
        Sticker("cg7", "https://lh3.googleusercontent.com/aida-public/AB6AXuDfvH5Ify6B1DugRgAxLi57kQl44LoLFZ67Fg69Qn2sVDOMGA5R2v6Z4mCsWCQOA4G4LlkI2vPqVhOUUfVvoozskbx4sqI0iWrf_TLMA1PSJOno56ZqQ1drFytByls8sItP9iN7GIPi_GmuFQEWRvn5lrw6B86u4LuP8-q3fEw_cMleNGIvAxkc9yVHSXUQfskGGv27HJjbS7P6W4-zjyp-jUOX9qe_Y31BtphOusHE0ZV-g44ENupzPAGAyeGkSt1oqWeZtbz-Lqc", "Kissing neon cat with glowing heart"),
        Sticker("cg8", "https://lh3.googleusercontent.com/aida-public/AB6AXuBkPYZygTd94tWN2w1m8zYb8JUWLk22aQJd792CcrB_9ZboA5J7QBoJSPI9ES0g4kCWoUbkuTBhcidQQ5EMpuBBwAWpFDsvXRzw6VpjNKmBQwQ9Ddb6jk4ZiQwCSgJe-GWu7j3VV-wNkRHl71DB3TnM_aTGd60LSH1vBFvSS22Da9xsA34xDR1Fm-oQVbXciqYC0-MDRM8kI0HLqxghc7WooZ3r6Zwv98gnGAtMn0OVWicUHqAmza2Xl9Z5JCwyweg0u4ADG-EIU38", "Holographic heart eyed cat"),
        Sticker("cg9", "https://lh3.googleusercontent.com/aida-public/AB6AXuCbQKu4xi2rSFCUmECsOd0jlZrmd0WpriDRIgllI2siRHpYl-P3QzfnNEgS27qh5aWwna4SOOLfuC3rlJQE6rAhrh7Cc3ocLu5fQRAf4k4hmVHWX9EKWMtYt2gAi1EQ1FwPca9BwaygkQlTYE7l9Uk98EucLQHHekHNnIqDNDW6Z7wi6k14TfnfSTJUfoagrqF69CXurRPfjq0kx6f15kPKK6PObH6K1IUUgbgQspKGXRcqlS9pBpJ6tR8XPdd45C5qtAMHc2h_h2o", "Cool blue cat with sunglasses")
    )

    val retroStickers = listOf(
        Sticker("rv1", "https://lh3.googleusercontent.com/aida-public/AB6AXuDSlXNY5xsvukq1cD3BhF6kpieE4o3vgOZk8YylLlfBhEquaGujYIHv4QQ9rAub4A1DRMFUzHjM5xKHdC4OOWlgHdsfpPqOtx19A8WQpiXCfpQib90cmaTUY9Sx-DoPGsbRmU5dJHDGht4bQiF6BQJx_DnuOAOJ0QvkzKsheFIsaS6MRg__iO_2QRhKn_Eo8Yl1PaD_58ALnKDr2xow6qNf4XwSrYsK3L95amcKQHj92dp8jVKK-v13i9K328iNg706PgT3oGpFGg0", "3D retro pink sunglasses"),
        Sticker("rv2", "https://lh3.googleusercontent.com/aida-public/AB6AXuDCEhsusl_vHLmq-btvaoNk1RDEPzfyw71BI-3r_cnoWB-90By9kBKV6r5LO1ztM91Re5YPVDne5hUFsdWTGu-Rz-hGJPmFvffVsZrzyZ1BRTz0C9H-XLvSEHeJ1PIBy_p5I7u1V9RDezS6GKLziKOCN4R_4yIxyymTGy3qGOfU7_UMj7d8x0UiewTNVcLSQ5w2JcGXmn9RJbQ1Kp5nWHcYpO9SW9dEsF-ydCoomKB_I6lbpnO_2PFxzq2Q841Mh7jFk6sLDAO8XmI", "Neon custom music cassette"),
        Sticker("rv3", "https://lh3.googleusercontent.com/aida-public/AB6AXuBLcaWm94WGhTW8OL39wl6eJLZaONcyYcS-UIINRH2r7ISEAhVvidmpk_MCKMlaX_-lujSpoXkSPf_pl5WmPibKdRAf1kSSjfvnT1tF5rfyVzIncJ3P21TufIHh2h2ju0G9mNPcNMdOE8vsJMb97-4gIqPTo2lAU0oyk5Noqsaso3V893lU4CWilt_2Mb5NEp5cfJ5LIT7KE9-UvUdH0Nj1DGP-rRCP28AmXgm-1PZ-tuGxDF1zfLcmSK0qxX7cw1f-_aua7jB-USQ", "Vibrant retro neon controller"),
        Sticker("rv4", "https://lh3.googleusercontent.com/aida-public/AB6AXuCeoa8xa0cADP6inuW_CpGPR-JPxaczWe4Aoo6UdrRQSn5WOXsTMd2obORFXfcVWB-cxXH7L60DlknsBz9X2JUTN8i18ZQKTUDjcwGtr5iiJsDF2kg4kPxDpCSArbZq0gDTlc0M6_snVTh4VrOzBLkS4DMOMEYldLEZ9DvfqSf9GyfuHhz2qr2xGqVLIJDPpncG0vXKcSiv2qS_h8qZX-cGHm0cefY5NqI3MkWBRFn4WldLgZIo0vcThe70g8MOVJ3iMzcf9EGQIok", "Classic TV glitch"),
        Sticker("rv5", "https://lh3.googleusercontent.com/aida-public/AB6AXuDagfO1l03F6HIBdaVWwgDl3ybc6Z2j96yb30QhoP-4PNMG6du7Dg_UxQqlhPjJbxkooQoeTt5OsqAKSMALbtPYG_g33yKB8N_4ZJ8VUvZRRxbRR5jS8nhn_HFE8iPUZLPfgkA8MhzgdDeU8KNelwao0mC2blLZDBi01TrHdnru6u5ld0IKMkR_tc-7AewSKhpXvLHAoijgJgxsXBBma3FPydOHig3owZJ3zr7srRsmQGNjJ75P27eaFaipjWGcLSIS6mBxOihR-E8", "3D retro radio player icon"),
        Sticker("rv6", "https://lh3.googleusercontent.com/aida-public/AB6AXuD4ZhDTOSLfuhC6CErv2os-uZ1lEgF1hjfxv_g6mWgBDC0HWgOeuZ4TVW7k8CV4Deh-oKoh52NtKfvA7-9ePz6k8pUddkwVD8S3EUS7AN5lCBLwl0iKXqOCJuutNtAZYow-zMTStglGipl7CbopPzi9oNvzY9cRb-bfAwSQJcxpNItFg3ak7BpyawDIydn40k9uUyghOn6NjIHkKIr2_lYpXwbsGSwXl6GRMdMGWnJkXWUsCZexs3F-7avHq8b4Ej9FPnvXOUJ0oNw", "Holographic retro palm design")
    )

    val hamsterSticker = Sticker("hams1", "https://lh3.googleusercontent.com/aida-public/AB6AXuB_Z0tqDaC3KJVQ3A6aBXvdaiZwlLGqBgvZdC_z0ClI1HEAN89XuPVS3IFXXrQReuzm3VlVdhV4P0EW73kRmqoMGDyALMdWafrpY-4Yn5niG-2yrSBgL0dEriunRsqvZ92O8za8DmAajIfFNL_Ew53xRDUeRwKVKcdshYFnIW5jZah1NpWcm76G9iNJgw_QolKpqw-5l-giHkcDD52SKgFLnmlmgD948Bajuedke3tGzv4s7-SO-tQxNGvKnVSH0mnBQGu17OVBwh0", "Happy cute hamster sticker")
    val fireSticker = Sticker("fire1", "https://lh3.googleusercontent.com/aida-public/AB6AXuBvjckPmaEJe33G5FxRwVtQ60fWzDPZz2GrZsArtNmrbGN_yVsnTBAjbtugbpkODuG7cRtLujjI-FTJo5ZxEw9J0sGBHmt03d0VzRZIN8jvj2P-OEViUE1Wu0qt2OjHKfsbByVw_sKdcnt-viCgj1WN9prXDsMQa6h_2uINZr7-bDmWO9RsNsOeOh3hGjCJNMZPokDKCgPYL3YZHqDtf7QvirxzP_NxVsdqW7O0K7iSjEpBK3Azu_5aoD8yZ9-zzcOFyRVs4vHzR3U", "Animated golden fire logo sticker")
    val ramenSticker = Sticker("ramen1", "https://lh3.googleusercontent.com/aida-public/AB6AXuBcte3nF0yeSbO7bXNMfcElAcViJkKcbT91QHXIv9Kzs8t3KND9ucDu4f5_bA1BAeCHVT15v-zKdrxbuBOHEyPv6qL-hD-qRlKDb-KnXyugFCQyMuAdP9S_QQkuu2xDrFvKj46nO5IvPCJT1owNBkOtvYw0r6RsZT28oiRlyf3arFXSAkH4KBdb68_HwemUfPfF4Sc744tR7x-FK6ka5cY9fffRbjxhk46tWt35qMTF8DNBOOva3aSavdSdQGK05A7IcYCMYHkLJlU", "Bowl of kawaii ramen sticker")
    val alienSticker = Sticker("alien1", "https://lh3.googleusercontent.com/aida-public/AB6AXuBjYsC84cMe-ADYWOhyTFM62I1BKAW1cw4vy2wcuXgJWnAmO7fXJSGFhgr_qP77F9uw7Bx5XsGdrEe6Xnfu16MKL29una2RDVoRKlldqirlh_6CdCmVInngVH3vDa7J0WSjtwCSmIVbfjtIc7jdDzZKZU5WzOQxt68V-rSh_3EVGG1x5zqKbEav2C_-oI6Oc4EifdWCn47r_Cnv94ZrnhKrK9_0UsbrlMLTmlG6CsMQcOZJKgjTgfXTP-uGm8CgDZSCGgYuKUqHp6s", "Friendly green alien sticker")

    val trendingPacks = listOf(
        StickerPack("cyber_gatos", "Cyber Gatos", "NeonMochi", cyberGatosStickers, "Animals", isPremium = true),
        StickerPack("retro_vibes", "Retro Vibes", "Synthwave_Artist", retroStickers, "Gaming")
    )

    val popularPacks = listOf(
        StickerPack("happy_hamsters", "Happy Hamsters", "Hammy & Friends", listOf(hamsterSticker), "Animals", totalStickers = 24),
        StickerPack("emoji_on_fire", "Emoji On Fire", "FireDesign Studio", listOf(fireSticker), "Funny", isExclusive = true, totalStickers = 32),
        StickerPack("ramen_life", "Ramen Life", "Chef Kawaii", listOf(ramenSticker), "Love", totalStickers = 18),
        StickerPack("outer_space", "Outer Space", "Galactic Ink", listOf(alienSticker), "Gaming", totalStickers = 40)
    )

    // Editor initial isolated portrait image sample
    const val defaultEditorStickerUrl = "https://lh3.googleusercontent.com/aida-public/AB6AXuA9hmtX4zI7nQcF7Vmyk5uc1sQt8HSCsYpZkIIUYgHf5kSawHSk9wjJXIJewCdSLlYv3SWK_cFsmbv4sBOIEXJq-8QM8kohJ74Zju0HRy_0rkQXI9znqk1F78jkXpgQo8v6qeyYETOSaq4GausT79Dlmz8wpTiySAzV3U4XQTSApyqlvAIXeDz2iNDee1ynz_aaySEyJQLNmjtDWEbRCifxyirmMWfJGT7FuOBmMr-oUMwZITL5DTazl0D2DZQkDd4245WgiRRcJ30"
    
    // Onboarding graphic resource
    const val onboardingHeroStickerUrl = "https://lh3.googleusercontent.com/aida-public/AB6AXuCkAzBBb1__SVPkIuHkfcDl4zyruX49g19VhCK4bZZ2_PDf3BUYpk7ksR1Z3BMAcTeArsiyBSbv2UrKnhaNPrxoui8QA8yiMABXnNn-6dnErCYv_i58RPdsqCQexECcnFTi-EYWAk_pK4sRDdl2RH9cAnrPTZvP9ezALqXMZuHJYmKxxk4pXj6-XP6W6yLgO-2VZhrjP3BIAeI-5UKhMT0F5jrniqqupYChtd8EI4Gb5mFRtiQ3Qi3VgXgAfNAeKWYcTDMUs2SRTDY"
    
    // Avatar picture URL
    const val profileAvatarUrl = "https://lh3.googleusercontent.com/aida-public/AB6AXuC9ceainRre_8dWZ_Pyjjgy3svsrxKmotvJhGWt0NM7a4AsqBV9eNHOcIbnq2nWzbocBh-FR_O29iCzwQCGqKyC0-LWj9b3MnKbWxG97tKrzcJ4hG0co1ooyshCUzotds7vcXWGdtfmGlFKR7EcOnfNVkQW5vgZ1cRG-UQf4r7PNy9XvLEsJc2YhuT6CXNiyFVklSGlEMod8Qg790QESXP8_fNwquBCzmKKApJf7Xe40ypwp0joP26AY6zY7c6F3DxddF1V1Ttdk_s"
}
