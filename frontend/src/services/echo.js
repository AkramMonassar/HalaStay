import Echo from 'laravel-echo'
import Pusher from 'pusher-js'

let echo = null

export function initEcho(token) {
  if (echo) return echo

  window.Pusher = Pusher
  echo = new Echo({
    broadcaster: 'pusher',
    key: import.meta.env.VITE_PUSHER_APP_KEY,
    cluster: import.meta.env.VITE_PUSHER_APP_CLUSTER,
    forceTLS: true,
    enabledTransports: ['ws', 'wss'],
    authEndpoint: 'http://localhost:8000/broadcasting/auth',
    auth: {
      headers: {
        Authorization: `Bearer ${token}`,
        Accept: 'application/json',
      },
    },
  })

  return echo
}

export function disconnectEcho() {
  if (echo) {
    echo.disconnect()
    echo = null
  }
}