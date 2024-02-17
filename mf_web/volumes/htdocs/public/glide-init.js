const glideOptions = document.getElementById('glide-init-script').dataset;

const glide = new Glide('.glide', {gap: parseInt(glideOptions.gap)}).mount()

glide.on('mount.after', function () {
    glide.update();
});
glide.mount();