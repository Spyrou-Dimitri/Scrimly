export const AvatarPreview = {
    inputAvatar: document.getElementById('avatarUpload'),
    imagePreview: document.getElementById('imagePreview'),
    placeholder: document.getElementById('avatar-preview-placeholder'),
    deleteAvatarButton: document.getElementById('deleteAvatar'),
    objectUrl: null,

    revokePreviewUrl() {
        if (this.objectUrl) {
            URL.revokeObjectURL(this.objectUrl);
            this.objectUrl = null;
        }
    },

    showPlaceholder() {
        this.revokePreviewUrl();
        this.imagePreview.removeAttribute('src');
        this.imagePreview.classList.add('hidden');
        this.placeholder?.classList.remove('hidden');
    },

    showPreview(file) {
        if (!file.type.startsWith('image/')) {
            return;
        }

        this.revokePreviewUrl();
        this.objectUrl = URL.createObjectURL(file);
        this.imagePreview.src = this.objectUrl;
        this.imagePreview.classList.remove('hidden');
        this.deleteAvatarButton.classList.remove('hidden');
        this.placeholder?.classList.add('hidden');
    },

    listenForChange() {
        this.inputAvatar.addEventListener('change', () => {
            const file = this.inputAvatar.files?.[0];
            if (file) {
                this.showPreview(file);
            } else {
                this.showPlaceholder();
            }
        });

        this.deleteAvatarButton.addEventListener('click', () => {
            this.inputAvatar.value = '';
            this.deleteAvatarButton.classList.add('hidden');
            this.showPlaceholder();
        });
    },

    init() {
        if (!this.inputAvatar || !this.imagePreview) {
            return;
        }

        this.listenForChange();
    },
};
