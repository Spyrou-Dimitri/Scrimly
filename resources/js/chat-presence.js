document.addEventListener('alpine:init', () => {
    window.Alpine.data('chatPresence', (teamId, currentUserId) => ({
        onlineMembers: [],
        typingMembers: {},
        typingTimer: {},     
        
        init(){
            const channel = window.Echo.join(`presence.chat.${teamId}`);

            channel.here((members) => {
                this.onlineMembers = members;
            });

            channel.joining((member) => {
                this.onlineMembers.push(member);
            });
            
            channel.leaving((member) => {
                this.onlineMembers = this.onlineMembers.filter((m) => m.id !== member.id);
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
    }));
    
});
