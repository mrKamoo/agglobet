<template>
  <div 
    class="bg-white overflow-hidden shadow-sm sm:rounded-lg border border-gray-100 flex flex-col"
    :class="[height]"
  >
    <!-- Chat Header -->
    <div class="px-6 py-4 bg-gradient-to-r from-indigo-600 to-indigo-700 text-white flex justify-between items-center shrink-0">
      <div class="flex items-center space-x-2">
        <span class="relative flex h-3 w-3">
          <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-green-400 opacity-75"></span>
          <span class="relative inline-flex rounded-full h-3 w-3 bg-green-500"></span>
        </span>
        <h3 class="text-lg font-semibold tracking-wide">Discussion en direct</h3>
      </div>
      <div class="text-xs opacity-75 font-medium">
        {{ messages.length }} message{{ messages.length > 1 ? 's' : '' }}
      </div>
    </div>

    <!-- Chat Messages list -->
    <div 
      ref="messageContainer"
      class="flex-1 overflow-y-auto p-6 space-y-4 bg-gray-50"
      @scroll="handleScroll"
    >
      <div v-if="loading && messages.length === 0" class="flex flex-col items-center justify-center h-full space-y-2">
        <svg class="animate-spin h-8 w-8 text-indigo-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
          <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
          <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
        </svg>
        <span class="text-sm text-gray-500 font-medium">Chargement des messages...</span>
      </div>

      <div v-else-if="messages.length === 0" class="flex flex-col items-center justify-center h-full text-center p-6">
        <div class="p-3 bg-indigo-50 rounded-full text-indigo-500 mb-2">
          <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"></path>
          </svg>
        </div>
        <p class="text-gray-500 font-medium">Aucun message pour le moment.</p>
        <p class="text-xs text-gray-400 mt-1">Soyez le premier à lancer la discussion !</p>
      </div>

      <div 
        v-for="msg in messages" 
        :key="msg.id" 
        class="flex flex-col group relative"
        :class="msg.user_id === currentUserId ? 'items-end' : 'items-start'"
      >
        <!-- Message metadata -->
        <div class="flex items-center space-x-1.5 mb-1 px-1 text-xs">
          <span 
            class="font-semibold text-gray-700"
            :class="{'text-indigo-600': msg.user_id === currentUserId}"
          >
            {{ msg.user.name }}
          </span>
          <span 
            v-if="msg.user.is_admin" 
            class="inline-flex items-center px-1.5 py-0.5 rounded text-[10px] font-bold bg-red-100 text-red-800"
          >
            Admin
          </span>
          <span 
            v-if="msg.user.rank !== undefined && msg.user.points !== undefined"
            class="inline-flex items-center px-1.5 py-0.5 rounded text-[10px] font-medium bg-indigo-50 text-indigo-750 border border-indigo-100"
            :title="`Rang: ${msg.user.rank}e | Total: ${msg.user.points} points`"
          >
            {{ msg.user.rank }}ᵉ • {{ msg.user.points }} pts
          </span>
          <span class="text-gray-400">•</span>
          <span class="text-gray-400" :title="formatFullDate(msg.created_at)">
            {{ formatTime(msg.created_at) }}
          </span>
        </div>

        <!-- Message bubble & reaction trigger -->
        <div 
          class="flex items-center space-x-2 max-w-[85%] relative"
          :class="msg.user_id === currentUserId ? 'flex-row-reverse space-x-reverse' : 'flex-row'"
        >
          <!-- Bubble -->
          <div 
            class="rounded-2xl px-4 py-2.5 text-sm shadow-sm transition-all duration-200 hover:shadow-md whitespace-pre-wrap break-words"
            :class="[
              msg.user_id === currentUserId 
                ? 'bg-indigo-600 text-white rounded-tr-none' 
                : 'bg-white text-gray-800 border border-gray-100 rounded-tl-none'
            ]"
          >
            {{ msg.message }}
          </div>

          <!-- Add Reaction Button (visible on hover) -->
          <div class="relative">
            <button 
              type="button" 
              @click.stop="toggleReactionMenu(msg.id)"
              class="opacity-0 group-hover:opacity-100 transition-opacity p-1 bg-white hover:bg-indigo-50 border border-gray-200 rounded-full text-gray-400 hover:text-indigo-600 focus:outline-none shadow-sm cursor-pointer shrink-0"
              title="Réagir"
            >
              <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.828 14.828a4 4 0 01-5.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
              </svg>
            </button>

            <!-- Reaction Picker Popover -->
            <div 
              v-if="activeReactionMenuMessageId === msg.id"
              class="absolute bottom-full mb-1 z-50 bg-white border border-gray-200 rounded-full shadow-lg px-2 py-1 flex items-center space-x-1"
              :class="msg.user_id === currentUserId ? 'right-0' : 'left-0'"
            >
              <button 
                v-for="emoji in reactionEmojis" 
                :key="emoji"
                type="button"
                @click="toggleReaction(msg, emoji)"
                class="hover:scale-125 transition-transform p-0.5 text-base focus:outline-none cursor-pointer"
              >
                {{ emoji }}
              </button>
            </div>
          </div>
        </div>

        <!-- Rendered reactions list below bubble -->
        <div 
          v-if="msg.reactions && msg.reactions.length > 0" 
          class="flex flex-wrap gap-1 mt-1 px-1"
        >
          <button
            v-for="group in getGroupedReactions(msg)"
            :key="group.emoji"
            type="button"
            @click="toggleReaction(msg, group.emoji)"
            class="inline-flex items-center space-x-1 px-2 py-0.5 rounded-full text-xs font-semibold border transition-all cursor-pointer"
            :class="[
              group.userReacted 
                ? 'bg-indigo-50 border-indigo-300 text-indigo-700 hover:bg-indigo-100' 
                : 'bg-white border-gray-200 text-gray-600 hover:bg-gray-50'
            ]"
            :title="`Réagi par ${group.count} participant(s)`"
          >
            <span>{{ group.emoji }}</span>
            <span class="text-[10px]">{{ group.count }}</span>
          </button>
        </div>
      </div>
    </div>

    <!-- Chat input area -->
    <div class="p-4 bg-white border-t border-gray-100 shrink-0">
      <form @submit.prevent="sendMessage" class="flex flex-col space-y-2">
        <div class="flex items-stretch space-x-2 relative">
          <!-- Emoji Picker Toggle & Popover -->
          <div ref="emojiPickerContainer" class="relative flex items-center">
            <button 
              type="button"
              @click.stop="toggleEmojiPicker"
              class="inline-flex items-center justify-center p-2 text-gray-500 hover:text-indigo-600 hover:bg-indigo-50 rounded-lg transition-colors focus:outline-none"
              title="Ajouter un emoji"
            >
              <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.828 14.828a4 4 0 01-5.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
              </svg>
            </button>

            <!-- Emoji Picker Popover -->
            <div 
              v-if="showEmojiPicker"
              class="absolute bottom-full mb-2 left-0 bg-white border border-gray-200 rounded-xl shadow-xl p-3 z-50 w-64"
            >
              <div class="grid grid-cols-6 gap-1.5">
                <button 
                  v-for="emoji in popularEmojis" 
                  :key="emoji"
                  type="button"
                  @click="addEmoji(emoji)"
                  class="text-xl p-1 hover:bg-gray-100 rounded transition-colors text-center focus:outline-none"
                >
                  {{ emoji }}
                </button>
              </div>
            </div>
          </div>

          <textarea 
            ref="messageTextarea"
            v-model="newMessage"
            rows="1"
            class="flex-1 resize-none rounded-lg border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 text-sm py-2 px-3 placeholder-gray-400 transition-colors"
            placeholder="Écrivez votre message..."
            maxlength="1000"
            @keydown.enter.prevent="handleEnter"
            :disabled="sending"
            @focus="closeEmojiPicker"
          ></textarea>

          <button 
            type="submit"
            :disabled="sending || !newMessage.trim()"
            class="inline-flex items-center justify-center px-4 py-2 bg-indigo-600 hover:bg-indigo-700 disabled:opacity-50 text-white rounded-lg transition-colors font-medium text-sm focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 shrink-0"
          >
            <svg v-if="sending" class="animate-spin h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
              <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
              <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
            </svg>
            <span v-else>Envoyer</span>
          </button>
        </div>
        <div class="flex justify-between items-center text-[10px] text-gray-400 px-1">
          <span>Appuyez sur Entrée pour envoyer</span>
          <span :class="{'text-red-500': newMessage.length >= 950}">
            {{ newMessage.length }} / 1000
          </span>
        </div>
      </form>
    </div>
  </div>
</template>

<script>
import axios from 'axios';

export default {
  name: 'DashboardChat',
  props: {
    currentUserId: {
      type: Number,
      required: true
    },
    height: {
      type: String,
      default: 'h-[500px]'
    }
  },
  data() {
    return {
      messages: [],
      newMessage: '',
      loading: true,
      sending: false,
      pollingInterval: null,
      userScrolledUp: false,
      showEmojiPicker: false,
      activeReactionMenuMessageId: null,
      reactionEmojis: ['👍', '❤️', '😂', '🔥', '👏', '⚽'],
      popularEmojis: [
        '😀', '😂', '😉', '😍', '😎', '😮',
        '😢', '😡', '😱', '👍', '👎', '👏',
        '🙌', '👊', '🤝', '🔥', '💪', '🎉',
        '🥳', '⚽', '🏆', '🎯', '🥅', '🍺',
        '❤️', '⚡', '💡', '🤫', '👀', '🚀'
      ]
    };
  },
  mounted() {
    this.fetchMessages();
    
    // Configurer le polling (toutes les 5 secondes)
    this.pollingInterval = setInterval(this.fetchMessages, 5000);
    
    // Clic en dehors pour fermer l'emoji picker
    document.addEventListener('click', this.handleOutsideClick);
  },
  beforeUnmount() {
    if (this.pollingInterval) {
      clearInterval(this.pollingInterval);
    }
    document.removeEventListener('click', this.handleOutsideClick);
  },
  methods: {
    async fetchMessages() {
      try {
        const response = await axios.get('/api/chat-messages');
        
        // Vérifier si des nouveaux messages sont apparus avant de mettre à jour pour garder le scroll au besoin
        const messageCountBefore = this.messages.length;
        const lastMessageIdBefore = messageCountBefore > 0 ? this.messages[messageCountBefore - 1].id : null;
        
        this.messages = response.data;
        this.loading = false;
        
        const messageCountAfter = this.messages.length;
        const lastMessageIdAfter = messageCountAfter > 0 ? this.messages[messageCountAfter - 1].id : null;

        // Faire défiler vers le bas si l'utilisateur n'a pas scrollé vers le haut,
        // ou si c'est le chargement initial, ou si le dernier message a changé
        if (!this.userScrolledUp || lastMessageIdBefore !== lastMessageIdAfter) {
          this.scrollToBottom();
        }
      } catch (error) {
        console.error('Erreur lors de la récupération des messages du tchat:', error);
      }
    },
    async sendMessage() {
      const trimmed = this.newMessage.trim();
      if (!trimmed || this.sending) return;

      this.sending = true;
      const messageToSend = trimmed;
      this.newMessage = ''; // Vider le champ immédiatement pour une sensation de réactivité
      this.closeEmojiPicker();

      try {
        const response = await axios.post('/api/chat-messages', {
          message: messageToSend
        });
        
        this.messages.push(response.data);
        this.userScrolledUp = false; // Forcer le scroll vers le bas car l'utilisateur a écrit
        this.scrollToBottom();
      } catch (error) {
        console.error("Erreur lors de l'envoi du message:", error);
        // Restaurer le texte en cas d'erreur
        this.newMessage = messageToSend;
        alert("Impossible d'envoyer le message. Veuillez réessayer.");
      } finally {
        this.sending = false;
      }
    },
    handleEnter(event) {
      // Si on n'appuie pas sur Shift+Entrée, on envoie
      if (!event.shiftKey) {
        this.sendMessage();
      } else {
        // Laisser le comportement par défaut (saut de ligne)
      }
    },
    scrollToBottom() {
      this.$nextTick(() => {
        const container = this.$refs.messageContainer;
        if (container) {
          container.scrollTop = container.scrollHeight;
        }
      });
    },
    handleScroll() {
      const container = this.$refs.messageContainer;
      if (!container) return;
      
      // Si l'utilisateur est à plus de 50px du bas, on considère qu'il a scrollé vers le haut
      const isAtBottom = container.scrollHeight - container.scrollTop - container.clientHeight < 50;
      this.userScrolledUp = !isAtBottom;
    },
    toggleEmojiPicker() {
      this.showEmojiPicker = !this.showEmojiPicker;
    },
    closeEmojiPicker() {
      this.showEmojiPicker = false;
    },
    addEmoji(emoji) {
      const textarea = this.$refs.messageTextarea;
      if (!textarea) {
        this.newMessage += emoji;
        return;
      }

      const start = textarea.selectionStart;
      const end = textarea.selectionEnd;
      const text = this.newMessage;
      
      this.newMessage = text.substring(0, start) + emoji + text.substring(end);
      
      // Focus et repositionnement du curseur
      this.$nextTick(() => {
        textarea.focus();
        const newCursorPos = start + emoji.length;
        textarea.setSelectionRange(newCursorPos, newCursorPos);
      });
    },
    toggleReactionMenu(messageId) {
      if (this.activeReactionMenuMessageId === messageId) {
        this.activeReactionMenuMessageId = null;
      } else {
        this.activeReactionMenuMessageId = messageId;
      }
    },
    async toggleReaction(message, emoji) {
      this.activeReactionMenuMessageId = null;
      try {
        const response = await axios.post(`/api/chat-messages/${message.id}/react`, {
          emoji: emoji
        });
        message.reactions = response.data;
      } catch (error) {
        console.error("Erreur lors de la réaction au message:", error);
      }
    },
    getGroupedReactions(message) {
      if (!message.reactions) return [];
      
      const groups = {};
      message.reactions.forEach(r => {
        if (!groups[r.emoji]) {
          groups[r.emoji] = {
            emoji: r.emoji,
            count: 0,
            userReacted: false
          };
        }
        groups[r.emoji].count++;
        if (r.user_id === this.currentUserId) {
          groups[r.emoji].userReacted = true;
        }
      });
      
      return Object.values(groups);
    },
    handleOutsideClick(event) {
      const picker = this.$refs.emojiPickerContainer;
      if (picker && !picker.contains(event.target)) {
        this.closeEmojiPicker();
      }
      this.activeReactionMenuMessageId = null;
    },
    formatTime(dateString) {
      if (!dateString) return '';
      const date = new Date(dateString);
      
      // Format simple HH:MM
      const hours = date.getHours().toString().padStart(2, '0');
      const minutes = date.getMinutes().toString().padStart(2, '0');
      
      // Si c'est aujourd'hui, afficher uniquement l'heure.
      // Sinon afficher aussi le jour/mois.
      const today = new Date();
      if (date.toDateString() === today.toDateString()) {
        return `${hours}:${minutes}`;
      } else {
        const day = date.getDate().toString().padStart(2, '0');
        const month = (date.getMonth() + 1).toString().padStart(2, '0');
        return `${day}/${month} ${hours}:${minutes}`;
      }
    },
    formatFullDate(dateString) {
      if (!dateString) return '';
      const date = new Date(dateString);
      return date.toLocaleString('fr-FR');
    }
  }
};
</script>

<style scoped>
/* Scrollbar personnalisée élégante */
div::-webkit-scrollbar {
  width: 6px;
}
div::-webkit-scrollbar-track {
  background: transparent;
}
div::-webkit-scrollbar-thumb {
  background-color: rgba(156, 163, 175, 0.3);
  border-radius: 20px;
}
div::-webkit-scrollbar-thumb:hover {
  background-color: rgba(156, 163, 175, 0.5);
}
</style>
