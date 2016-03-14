ffmpeg -rtsp_transport tcp -i rtsp://user:password@localhost:554/rtsp -vcodec copy -f flv -r 25 -s 1920x1080 -an "rtmp://localhost/rtmp"

-an: Disable audio

-f: Force format

-r: Frame rate

-s: Video resolution

-vcodec: Set the video codec. "copy" means no decoding/encoding.
