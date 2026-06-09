document.addEventListener('alpine:init', () => {
    window.Alpine.store('presence', {
        onlineMembers: [],
        labels: {},
        currentUserId : null,
        channel : null,
        teamId : null,
        pendingReload : {},
        connect(teamId, currentUserId, labels = {}){
            const pendingReload = {};
            
            this.labels = labels;
            this.currentUserId = currentUserId;
            this.teamId = teamId;
            this.pendingReload = {};
            this.onlineMembers = [];
            
           

            this.channel = window.Echo.join(`presence.${teamId}`);
            const detectionReload = 3000;


            this.channel.here((members) => {
                this.onlineMembers = members;
            });

            this.channel.leaving((member) => {
                this.pendingReload[member.id] = setTimeout(() => {
                    delete this.pendingReload[member.id];
                    this.onlineMembers = this.onlineMembers.filter((m) => m.id !== member.id);
                        Livewire.dispatch('toast', [{
                            type: 'no-symbol',
                            message: `${member.username} ${this.labels.isOffline}`,
                        }]);
                }, detectionReload);
            });

            this.channel.joining((member) => {
                if (this.pendingReload[member.id]) {
                    clearTimeout(this.pendingReload[member.id]);
                    delete this.pendingReload[member.id];
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
        },
        isOnline(userId) {
            return this.onlineMembers.some((m) => m.id === userId);
        },
    });
});
