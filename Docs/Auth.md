Dobrze, zaprojektujmy kompleksowe rozwiązanie JWT dla całej platformy.

### 1. Schemat Działania JWT

```plaintext
System:
api.wartkinurt.pl/v1/
├── wartkinurt.pl (SPA)
├── dashboard.wartkinurt.pl (SPA)
└── Mobile Apps (Android/iOS)

JWT Flow:
1. Logowanie → otrzymanie access_token + refresh_token
2. access_token w Authorization header
3. refresh_token w secure HTTP-only cookie
```
