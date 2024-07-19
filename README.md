# IIM-A3-Symfony

## Installation

1. **Cloner le dépôt :**

   ```bash
   git clone https://votre-repo-url.git
   cd votre-repo

2. **Installer avec Composer :**

   ```bash
   composer install

3. **Changez .env.local :**

   ```bash
   DATABASE_URL="mysql://root:root@127.0.0.1:3306/Nom_De_La_DB?charset=utf8mb4"
   
   et taper dans votre terminal
   
   php bin/console lexik:jwt:generate-keypair

4. **Lancez le projet :**
   ```bash
   symfony server:start



