document.addEventListener('alpine:init', () => {
    window.Alpine.data('presence', (teamId, currentUserId, labels = {}) => ({
        onlineMembers: [],
        labels,
        init(){
            const channel = window.Echo.join(`presence.${teamId}`);
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
                if (member.id !== currentUserId && ! this.onlineMembers.some((m) => m.id === member.id)) {
                   
                        Livewire.dispatch('toast', [{
                            type: 'wifi',
                            message: `${member.username} ${this.labels.isOnline}`,
                        }]);
                }
                this.onlineMembers.push(member);
            });

        }
    }));
});