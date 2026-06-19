const mseQueueStreamOut = []
let mseSourceBufferStreamOut
let mseStreamingOutStarted = false
let ws_out
let observerStreamOut

function startPlayStreamOut(videoEl_StreamOut, url) {
  if (!videoEl_StreamOut) {
    // console.error('Video element is not present.')
    return
  }

  const mseOut = new MediaSource()
  videoEl_StreamOut.src = window.URL.createObjectURL(mseOut)

  mseOut.addEventListener('sourceopen', function () {
    ws_out = new WebSocket(url)
    ws_out.binaryType = 'arraybuffer'
    ws_out.onmessage = function (event) {
      const data = new Uint8Array(event.data)
      if (data[0] === 9) {
        let mimeCodecOut
        const decodedArrOut = data.slice(1)
        if (window.TextDecoder) {
          mimeCodecOut = new TextDecoder('utf-8').decode(decodedArrOut)
        } else {
          mimeCodecOut = Utf8ArrayToStr(decodedArrOut)
        }
        mseSourceBufferStreamOut = mseOut.addSourceBuffer('video/mp4; codecs="' + mimeCodecOut + '"')
        mseSourceBufferStreamOut.mode = 'segments'
        mseSourceBufferStreamOut.addEventListener('updateend', pushPacketStreamOut)
      } else {
        readPacketStreamOut(event.data)
      }
    }

    ws_out.onerror = function (error) {
      console.error('WebSocket error:', error)
    }
  }, false)

  // Observe DOM for removal of #mse-video
  observerStreamOut = new MutationObserver(() => {
    if (!document.body.contains(videoEl_StreamOut)) {
      stopProcesOutStream()
    }
  })
  observerStreamOut.observe(document.body, { childList: true, subtree: true })
}

function stopProcesOutStream() {
  // Close WebSocket
  if (ws_out && ws_out.readyState === WebSocket.OPEN) {
    ws_out.close()
  }

  // Clear MediaSource and SourceBuffer
  if (mseSourceBufferStreamOut) {
    mseSourceBufferStreamOut.removeEventListener('updateend', pushPacketStreamOut)
    mseSourceBufferStreamOut = null
  }

  // Stop MutationObserver
  if (observerStreamOut) {
    observerStreamOut.disconnect()
    observerStreamOut = null
  }

  mseQueueStreamOut.length = 0
  mseStreamingOutStarted = false
}

function pushPacketStreamOut() {
  const videoEl_StreamOut = document.querySelector('#mse-video-out')
  if (!videoEl_StreamOut) {
    stopProcesOutStream()
    return
  }

  let packetOut

  if (!mseSourceBufferStreamOut.updating) {
    if (mseQueueStreamOut.length > 0) {
      packetOut = mseQueueStreamOut.shift()
      mseSourceBufferStreamOut.appendBuffer(packetOut)
    } else {
      mseStreamingOutStarted = false
    }
  }

  if (videoEl_StreamOut.buffered.length > 0) {
    if (typeof document.hidden !== 'undefined' && document.hidden) {
      videoEl_StreamOut.currentTime = videoEl_StreamOut.buffered.end(videoEl_StreamOut.buffered.length - 1) - 0.5
    }
  }
}

function readPacketStreamOut(packet) {
  if (!mseStreamingOutStarted) {
    mseSourceBufferStreamOut.appendBuffer(packet)
    mseStreamingOutStarted = true
    return
  }
  mseQueueStreamOut.push(packet)
  if (!mseSourceBufferStreamOut.updating) {
    pushPacketStreamOut()
  }
}