const scriptRef = document.getElementById('glide-init-script');

new Glide('.glide', {gap: parseInt(scriptRef.dataset.gap)}).mount()

glide.on('mount.after', function () {
    glide.update();
});
glide.mount();