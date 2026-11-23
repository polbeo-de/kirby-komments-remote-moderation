# System responsibilities

The remote bridge protects and translates a small moderation contract. It does
not decide editorial policy; Komments remains the owner of comment state and
storage. The local console is the only place for Panel interaction and the
additional write confirmation required for a source marked `live`.

The browser communicates only with authenticated Kirby Panel routes. Those
routes delegate internally to the server-side remote client. HMAC keys never
leave the local PHP runtime or remote bridge configuration.
