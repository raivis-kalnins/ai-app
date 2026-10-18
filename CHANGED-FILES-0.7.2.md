# Changed in v0.7.2

- `includes/bootstrap.php`: one-time private owner activation on locked installations, without resetting existing unique passwords.
- `storage/.owner-login-once`: private, one-use bcrypt hash file. **Personalised; never publish.** Automatically deleted after activation.
- `VERSION`, `service-worker.js`, `CHANGELOG.md`, `README.md`: version and release documentation.
- `UPGRADE-0.7.2.md`: complete cPanel upload and security instructions.

No existing storage settings, provider keys, search data, hCaptcha credentials or chat history are bundled or overwritten.
