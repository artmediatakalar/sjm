import Echo from 'laravel-echo';
import Pusher from 'pusher-js';
window.Pusher = Pusher;

window.Echo = new Echo({
  broadcaster: 'pusher',
  key: import.meta.env.VITE_PUSHER_APP_KEY,
  cluster: import.meta.env.VITE_PUSHER_APP_CLUSTER,
  forceTLS: true,
});

window.Echo.connector.pusher.connection.bind('connected', function () {
  console.log('✅ Echo connected to Pusher!');
});

// ✅ Public channel test
window.Echo.channel('members')
  .listen('.BroadcastTest', (e) => {
    console.log('📡 BroadcastTest diterima:', e);
  });

// ✅ Private channel untuk sponsor/upline
if (window.userId) {
  console.log("⏳ Subscribe ke channel: upline." + window.userId);

  window.Echo.private(`upline.${window.userId}`)
    .listen('.NewMemberApproved', (e) => {
      console.log("📦 Member baru diterima:", e);
      toastr.success(`Member baru bergabung: ${e.name} (${e.username})`);
    });
} else {
  console.warn("❗ window.userId tidak ditemukan");
}
