const mseQueueStreamIn = []
let mseSourceBufferStreamIn
let mseStreamingInStarted = false
let ws_in
let observerStreamIn

function startPlayStreamIn(videoEl_StreamIn, url) {
  if (!videoEl_StreamIn) {
    // console.error('Video element is not present.')
    return
  }

  const mseIn = new MediaSource()
  videoEl_StreamIn.src = window.URL.createObjectURL(mseIn)

  mseIn.addEventListener('sourceopen', function () {
    ws_in = new WebSocket(url)
    ws_in.binaryType = 'arraybuffer'
    ws_in.onmessage = function (event) {
      const data = new Uint8Array(event.data)
      if (data[0] === 9) {
        let mimeCodecIn
        const decodedArrIn = data.slice(1)
        if (window.TextDecoder) {
          mimeCodecIn = new TextDecoder('utf-8').decode(decodedArrIn)
        } else {
          mimeCodecIn = Utf8ArrayToStr(decodedArrIn)
        }
        mseSourceBufferStreamIn = mseIn.addSourceBuffer('video/mp4; codecs="' + mimeCodecIn + '"')
        mseSourceBufferStreamIn.mode = 'segments'
        mseSourceBufferStreamIn.addEventListener('updateend', pushPacketStreamIn)
      } else {
        readPacketStreamIn(event.data)
      }
    }

    ws_in.onerror = function (error) {
      console.error('WebSocket error:', error)
    }
  }, false)

  // Observe DOM for removal of #mse-video
  observerStreamIn = new MutationObserver(() => {
    if (!document.body.contains(videoEl_StreamIn)) {
      stopProcesInStream()
    }
  })
  observerStreamIn.observe(document.body, { childList: true, subtree: true })
}

function stopProcesInStream() {
  // Close WebSocket
  if (ws_in && ws_in.readyState === WebSocket.OPEN) {
    ws_in.close()
  }

  // Clear MediaSource and SourceBuffer
  if (mseSourceBufferStreamIn) {
    mseSourceBufferStreamIn.removeEventListener('updateend', pushPacketStreamIn)
    mseSourceBufferStreamIn = null
  }

  // Stop MutationObserver
  if (observerStreamIn) {
    observerStreamIn.disconnect()
    observerStreamIn = null
  }

  mseQueueStreamIn.length = 0
  mseStreamingInStarted = false
}

function pushPacketStreamIn() {
  const videoEl_StreamIn = document.querySelector('#mse-video-in')
  if (!videoEl_StreamIn) {
    stopProcesInStream()
    return
  }

  let packetIn

  if (!mseSourceBufferStreamIn.updating) {
    if (mseQueueStreamIn.length > 0) {
      packetIn = mseQueueStreamIn.shift()
      mseSourceBufferStreamIn.appendBuffer(packetIn)
    } else {
      mseStreamingInStarted = false
    }
  }

  if (videoEl_StreamIn.buffered.length > 0) {
    if (typeof document.hidden !== 'undefined' && document.hidden) {
      videoEl_StreamIn.currentTime = videoEl_StreamIn.buffered.end(videoEl_StreamIn.buffered.length - 1) - 0.5
    }
  }
}

function readPacketStreamIn(packet) {
  if (!mseStreamingInStarted) {
    mseSourceBufferStreamIn.appendBuffer(packet)
    mseStreamingInStarted = true
    return
  }
  mseQueueStreamIn.push(packet)
  if (!mseSourceBufferStreamIn.updating) {
    pushPacketStreamIn()
  }
}