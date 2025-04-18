✅ **Solution technique - Backend Symfony (Dossier `back/`)**

#### 📦 Prérequis

- Docker / Docker Compose
- Make installé sur votre système (facultatif, sinon utiliser directement les commandes)

---

### 🏁 Démarrage rapide

```bash
cd back
make up
```

Cela exécute :
```bash
docker-compose up -d --build
```
ça permet de lancer les microsevices doker compose.

---


toujor dans le docier **back**

```bash
make init
```

Cela exécute :
- `make install` → Installe les dépendances PHP via Composer
- `make migrate` → Applique les migrations pour l’environnement de développement
- `make migrate-test` → Crée et applique les migrations pour l’environnement de test
- `make jwt` → Génère les clés JWT nécessaires à l’authentification

---

### 🧪 Exécuter les tests fonctionnels

```bash
make test
```

> Utilise `phpunit` avec une base de données configurée pour l’environnement `test`.


Par exemple, dans le fichier .env.test ajouter

```
DATABASE_URL="postgresql://appuser:password@database:5432/db_for_test?serverVersion=16&charset=utf8"
```


---

### 🔐 JWT

Les clés sont générées dans `config/jwt/`.

Si nécessaire, relancer :

```bash
make jwt
```

---

### 🔌 Autres commandes utiles

| Commande         | Description                                      |
|------------------|--------------------------------------------------|
| `make up`        | Lance les conteneurs Docker                     |
| `make down`      | Arrête les conteneurs Docker                    |
| `make shell`     | Ouvre un shell dans le conteneur `php`          |
| `make logs`      | Affiche les logs PHP en temps réel              |
| `make reset-db`  | Réinitialise entièrement les bases `dev` et `test` |

---

### 📬 Postman

#### 1. Importer la collection

Fichier : [`Alten.postman_collection.json`](./Alten.postman_collection.json)

#### 2. Définir les variables dans Postman

Accédez à l'onglet **"Variables d'environnement"** dans Postman et créez un nouvel environnement :

| Variable   | Valeur par défaut            |
|------------|------------------------------|
| `base_url` | `http://localhost:8000`      |
| `token`    | (Ajoutez le token après connexion via `/token`) |

Ou bien céer des variable de collection.


#### 3. Utiliser les routes dans Postman

Vous pouvez maintenant :

- Créer un compte via `POST /account` (User > Account)
- Récupérer un token via `POST /token` (User > Token)
- Utiliser ce token pour toutes les autres requêtes protégées (`Authorization: Bearer {{token}}`)

---

### 🛒 Routes disponibles

| Méthode | URL                   | Description                           | Authentification |
|--------:|-----------------------|---------------------------------------|------------------|
| POST    | `/products`           | Créer un produit                      | ✅ (admin only)  |
| GET     | `/products`           | Lister les produits                   | ❌               |
| GET     | `/products/{id}`      | Voir un produit                       | ❌               |
| PATCH   | `/products/{id}`      | Modifier un produit                   | ✅ (admin only)  |
| DELETE  | `/products/{id}`      | Supprimer un produit                  | ✅ (admin only)  |
| POST    | `/account`            | Créer un compte utilisateur           | ❌               |
| POST    | `/token`              | Connexion via email/password          | ❌               |
| GET     | `/cart`               | Voir son panier                       | ✅               |
| POST    | `/cart/add`           | Ajouter un produit au panier          | ✅               |
| POST    | `/cart/remove`        | Supprimer un produit du panier        | ✅               |
| GET     | `/wishlist`           | Voir sa liste d’envies                | ✅               |
| POST    | `/wishlist/add`       | Ajouter un produit à la wishlist      | ✅               |
| POST    | `/wishlist/remove`    | Retirer un produit de la wishlist     | ✅               |

---
