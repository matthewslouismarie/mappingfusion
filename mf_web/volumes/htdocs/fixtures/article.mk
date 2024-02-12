# Introduction

You've probably once found yourself in a situation like this:
You have a low-res (say 64x64) WAD texture, and want to apply a high-res (say 512x512) detail texture that is basically the original WAD texture, but in a higher resolution. Some of you are doing this in order to save on AllocBlock, others are probably making HD textures for existing maps, and some of you probably just wanna experiment. You've all come to the right place.

## Une histoire de fou

Here's the original texture:

![eau](https://i.imgur.com/68v3DJq.png)
*Des textures de qualité.*

Here's the WAD texture:


![eau](https://i.imgur.com/68v3DJq.png)
*Un level design de fou, c’est du jamais vu.*

If you used the original texture as the detail texture, with no changes, you'd get this in-game:

[https://i.imgur.com/VoaFdUV.png]

It looks detailed, but the colours are now messed up. "Aha", you say, "I can just desaturate the detail texture!"
At first glance, that sounds like a good workaround until you actually try it out:

## Un homme déchiré


![eau](https://i.imgur.com/68v3DJq.png)
*WAHOU !!! C’est incroyable ça !!!*

Yay, now the colours are alright, but it still looks bad and quite different than the original.

So, how do we do this *properly*?

<!--- Conseil -->
## Le petit conseil pour les mappeurs Mapping-Fusion
Un excellent moyen de s’améliorer en mapping et en modding, c’est de jouer à des mods ! Ça permet de voir ce qui marche (et ne marche pas) en terme de visuels, choix des textures, lumières, gameplay…