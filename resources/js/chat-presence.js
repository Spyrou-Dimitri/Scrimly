document.addEventListener('alpine:init', () => {
    window.Alpine.data('chatPresence', (teamId, currentUserId, labels = {}) => ({
        onlineMembers: [],
        typingMembers: {},
        typingTimer: {},
        labels,

        init(){
            const channel = window.Echo.join(`presence.chat.${teamId}`);
            const detectionReload = 3000;
            const pendingReload = {};
            channel.here((members) => {
                this.onlineMembers = members;
            });

            
            channel.leaving((member) => {
                pendingReload[member.id] = setTimeout(() => {
                    delete pendingReload[member.id];

                    this.onlineMembers = this.onlineMembers.filter((m) => m.id !== member.id);
                    Livewire.dispatch('toast', [{
                        type: 'no-symbol',
                        message: `${member.username} ${this.labels.isOffline}`,
                    }]);
                }, detectionReload);
            });

            channel.joining((member) => {
                if (pendingReload[member.id]) {
                    clearTimeout(pendingReload[member.id]);
                    delete pendingReload[member.id];
                    return;
                }
                if (member.id !== currentUserId && ! this.onlineMembers.includes(member)) {
                    Livewire.dispatch('toast', [{
                        type: 'wifi',
                        message: `${member.username} ${this.labels.isOnline}`,
                    }]);
                }

                    this.onlineMembers.push(member);
            });

            channel.listenForWhisper('typing', (e) => {
                if (e.id === currentUserId){
                    return;
                }
                this.typingMembers[e.id] = e.username;
                clearTimeout(this.typingTimer[e.id]);
                this.typingTimer[e.id] = setTimeout(() => {
                    delete this.typingMembers[e.id];
                }, 1000);
                
            });
            this.channel = channel;
            

        },
        notifyTyping(username){
            this.channel.whisper('typing', {
                id: currentUserId,
                username: username,
            });
        },
        get typingLabel() {
            const names = Object.values(this.typingMembers);
            if (names.length === 0) return '';
            if (names.length === 1) return `${names[0]} est en train d'ecrire...`;
            return `${names.length} membres sont en train d'ecrire...`;
        },

        get previewOnlineMembers() {
            return this.onlineMembers.slice(0, 3);
        },

        get previewOnlineNames() {
            return this.previewOnlineMembers.map((member) => member.username).join(', ');
        },

        get remainingOnlineCount() {
            return Math.max(0, this.onlineMembers.length - this.previewOnlineMembers.length);
        },

        get onlineStatusSuffix() {
            if (this.onlineMembers.length === 0) {
                return '';
            }

            if (this.remainingOnlineCount === 0) {
                return this.labels.onlineSuffix ?? ' sont en ligne';
            }

            if (this.remainingOnlineCount === 1) {
                return this.labels.onlineAndOneOther ?? ' et 1 autre sont en ligne';
            }

            return (this.labels.onlineAndOthers ?? ' et :count autres sont en ligne')
                .replace(':count', this.remainingOnlineCount);
        },
    }));

});
