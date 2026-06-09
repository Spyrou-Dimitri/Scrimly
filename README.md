# Scrimly

Plateforme de gestion d'équipes esport League of Legends.

## Sommaire

- [Contexte](#contexte)
- [Persona](#persona)
- [Scénario](#scénario)
- [Technologies](#technologies)
- [Comment installer le repo sur un ordinateur](#comment-installer-le-repo-sur-un-ordinateur)

## Contexte

Scrimly est une application web destinée aux équipes amateurs et semi-professionnelles de League of Legends. Elle centralise dans un seul outil tout ce dont une équipe a besoin pour s'organiser et progresser.

L'application permet de :

- Créer une équipe ou en rejoindre une via un code d'invitation unique à six caractères.
- Gérer le roster : titulaires, remplaçants, rôles dans l'équipe (coach, joueur, staff) et rôles en jeu (top, jungle, mid, adc, support).
- Lier le compte Riot de chaque joueur pour récupérer automatiquement son rang, son score d'elo, son ratio de victoires, son KDA et son champion favori via l'API Riot Games.
- Calculer automatiquement l'elo moyen des titulaires d'une équipe afin d'évaluer son niveau.
- Distribuer des devoirs (entraînements, vidéos à analyser, objectifs) avec sous-tâches, fichiers joints, liens et suivi des soumissions.
- Organiser des scrims (matchs d'entraînement) entre équipes : envoi et acceptation de demandes, planification, puis encodage des résultats partie par partie.
- Communiquer en temps réel grâce à une messagerie intégrée.
- Visualiser l'ensemble des échéances (scrims, devoirs, évènements) dans un calendrier.

L'objectif est de remplacer la dispersion habituelle des outils (tableurs, messageries diverses, notes éparses) par une plateforme unifiée, adaptée au vocabulaire et aux besoins spécifiques de League of Legends.

## Persona

### 1. Le coach d'une équipe semi-compétitive

Thomas, 28 ans, entraîne une équipe ambitieuse qui vise le haut niveau. Il passe ses soirées à revoir les parties de ses joueurs, à noter leurs points faibles et à leur donner des exercices ciblés. Il cherche constamment des adversaires de niveau équivalent pour des matchs d'entraînement et veut mesurer précisément la progression de chacun, partie après partie. Aujourd'hui, ses observations sont éparpillées entre des fichiers, des captures d'écran et des messages, et il perd beaucoup de temps à tout recompiler.

### 2. La joueuse titulaire

Léa, 21 ans, joue au poste d'ADC dans son équipe. Très assidue, elle s'entraîne plusieurs heures par jour et suit de près ses statistiques (rang, KDA, taux de victoire) pour identifier ce qu'elle doit améliorer. Elle veut que son staff ait une vision claire de son niveau, recevoir les consignes de son coach et connaître à l'avance les horaires des entraînements pour s'organiser.

### 3. La bande de potes qui veut monter une équipe

Cinq amis se retrouvent chaque semaine pour jouer ensemble, avant tout pour le plaisir. L'un d'eux joue le rôle d'organisateur : il rassemble le groupe, propose des créneaux et essaie de caler des matchs amicaux contre d'autres groupes. Leur principale difficulté est de coordonner les disponibilités de chacun et de garder une trace de qui est présent ou non.

### 4. Le manager / staff

Karim, 25 ans, ne joue pas mais s'occupe de toute la partie organisationnelle d'une équipe. Il recrute de nouveaux membres, répond aux personnes qui souhaitent rejoindre la structure, tient à jour la composition de l'effectif et veille au planning des entraînements et des matchs. Il a besoin d'un cadre clair pour gérer les arrivées et les départs sans confusion.

## Scénario

### Création et accès à une équipe

Pour la première fois, l'organisateur de la bande de potes décide de structurer leurs sessions. Depuis son tableau de bord, il clique sur « Créer une équipe » et renseigne le nom du groupe, un tag de quelques lettres, un logo, la langue, le serveur de jeu et l'objectif « Fun ». Une fois l'équipe validée, l'application génère automatiquement un code unique à six caractères.

Il copie ce code et l'envoie à ses quatre amis. Chacun se connecte de son côté, ouvre la page « Rejoindre une équipe », colle le code et se retrouve aussitôt rattaché à l'équipe. En quelques minutes, le groupe entier dispose d'un espace commun, sans que personne n'ait eu à remplir de longs formulaires.

### Recrutement et gestion du roster

Karim, le manager, souhaite renforcer l'effectif d'un poste manquant. Il ouvre la page des invitations et envoie une invitation à un joueur repéré, ou attend qu'un candidat postule spontanément. Lorsqu'une candidature arrive, il la voit apparaître dans la liste des demandes en attente ; il consulte le profil du candidat, puis l'accepte ou la refuse.

Une fois le joueur intégré, Karim ouvre le roster, le désigne comme titulaire et lui attribue son rôle en jeu (par exemple jungle) et son rôle dans l'équipe (joueur). Si le code d'équipe a trop circulé, il le régénère d'un clic pour éviter que des inconnus ne rejoignent le groupe.

### Profil joueur et statistiques

Léa vient d'intégrer l'équipe et veut que son staff connaisse son niveau réel. Depuis son profil, elle saisit son identifiant Riot (pseudo et tag). L'application interroge l'API Riot Games et récupère son rang, son tier, ses LP, son nombre de victoires et de défaites ainsi que ses dernières parties.

Sur sa fiche, Léa voit alors apparaître son score d'elo calculé, son taux de victoire, son KDA général et son champion favori. Ces informations alimentent automatiquement l'elo moyen des titulaires, ce qui permet à Karim et Thomas de situer le niveau global de l'équipe.

### Devoirs et entraînement

Avant la prochaine session, Thomas, le coach, veut faire travailler ses joueurs sur un point précis. Il crée un devoir intitulé « Analyse de la phase de lane », le décompose en sous-tâches (revoir la replay, noter trois erreurs, proposer une correction), y joint un fichier et un lien vers une vidéo, puis l'assigne à l'équipe.

Léa reçoit le devoir, ouvre sa fiche, coche les sous-tâches au fur et à mesure et dépose sa soumission. Elle laisse un commentaire pour poser une question ; Thomas lui répond directement dans le fil. Depuis sa vue d'ensemble, il suit qui a rendu son travail et qui est en retard.

### Calendrier

En début de semaine, l'organisateur de la bande de potes veut s'assurer que tout le monde sera présent. Il ouvre le calendrier de l'équipe, qui regroupe au même endroit les scrims planifiés, les devoirs à rendre et les évènements créés. Il clique sur une journée pour en voir le détail et ajoute un créneau d'entraînement. Chaque membre peut consulter ce calendrier pour connaître les échéances à venir et s'organiser.

### Chat en temps réel

Juste avant une session, Léa ouvre la messagerie de l'équipe pour prévenir qu'elle aura cinq minutes de retard. Son message s'affiche instantanément pour tous les membres connectés, sans rechargement de page. Thomas lui répond dans la foulée pour confirmer l'horaire, et l'organisateur en profite pour rappeler le canal vocal à rejoindre.

### Scrims

Le suivi d'un scrim se déroule en deux temps : d'abord la prise de contact entre deux équipes, puis l'encodage des résultats une fois les parties jouées.

#### Scénario A : la demande de scrim et son acceptation

Thomas cherche un match d'entraînement pour le week-end. Depuis la page « Trouver un scrim », il repère une équipe d'un niveau proche du sien et lui envoie une demande de scrim en précisant la date, l'heure, le nombre de parties souhaité et un court message de présentation.

De l'autre côté, le coach de l'équipe adverse retrouve cette demande dans ses demandes reçues. Il consulte les informations de l'équipe de Thomas, juge la proposition intéressante et accepte la demande. À cet instant, la demande passe au statut « accepté » et un scrim planifié est automatiquement créé pour les deux équipes. Il apparaît dans la liste des scrims et sur le calendrier de chacune, sans qu'aucune des deux n'ait à le ressaisir.

Si l'équipe adverse avait refusé ou laissé la demande sans réponse, Thomas l'aurait vu reflété dans le statut de sa demande et aurait pu en envoyer une autre à une équipe différente.

Le jour J, Thomas n'a rien à faire pour « lancer » le scrim. Une tâche planifiée s'exécute en arrière-plan chaque minute (commande `app:start-scrims`) : dès que la date et l'heure prévues sont atteintes, elle fait automatiquement passer le scrim du statut « planifié » au statut « en cours ». Thomas n'a donc plus qu'à se concentrer sur les parties.

#### Scénario B : l'encodage des scores après une partie

Le scrim a eu lieu et la première partie vient de se terminer. Thomas ouvre le scrim concerné et ajoute une game. Il indique le résultat (victoire ou défaite), la durée de la partie, puis encode les statistiques de chacun de ses titulaires : champion joué, kills, morts et assists. Il saisit également les compositions et les performances des joueurs adverses.

Dès la validation, l'application calcule automatiquement le KDA de chaque joueur, le total de kills de chaque camp et met à jour le bilan du scrim ainsi que le taux de victoire global de l'équipe. Thomas répète l'opération pour chaque partie jouée, et dispose ainsi d'un historique précis pour analyser la progression de son équipe lors des prochains entraînements.

## Technologies

### Backend

- PHP 8.4
- Laravel 13
- Livewire 4 et Flux UI (composants d'interface)
- Laravel Fortify (authentification, double authentification)
- Laravel Reverb (serveur de websockets pour le temps réel)
- Intervention Image (traitement des logos et avatars)
- Pest 4 (tests) et Laravel Pint (formatage du code)

### Frontend

- Vite 8
- Tailwind CSS 4
- FullCalendar (calendrier)
- ApexCharts (graphiques de statistiques)
- Laravel Echo et Pusher JS (client websockets)
- flag-icons (drapeaux des serveurs)

### Base de données

- MySQL (fourni par Laravel Herd en local)

### API externe

- Riot Games API (profils, rangs et historique de matchs League of Legends)

## Comment installer le repo sur un ordinateur

### Prérequis

- PHP 8.4 ou supérieur
- Composer
- Node.js et npm
- Git
- Laravel Herd (recommandé pour servir le site localement)

### 1. Cloner le dépôt

```bash
git clone <url-du-depot> scrimly
cd scrimly
```

### 2. Installer les dépendances

```bash
composer install
npm install
```

### 3. Créer le fichier d'environnement et le lien de stockage

```bash
cp .env.example .env
php artisan key:generate
```

Les avatars des utilisateurs et les logos d'équipe sont stockés sur le disque public. Créez le lien symbolique nécessaire pour que ces images soient accessibles depuis le navigateur :

```bash
php artisan storage:link
```

### 4. Configurer la clé API Riot

L'application a besoin d'une clé d'API Riot Games pour récupérer les profils et statistiques des joueurs. Les variables correspondantes sont déjà présentes dans `.env.example` (donc dans votre `.env` après l'étape 3), avec `RIOT_API_KEY` laissée vide :

```
RIOT_API_KEY=
RIOT_API_REGION_ROUTING=europe
RIOT_API_REGION_PLATFORM=euw1
```

Il ne vous reste qu'à renseigner votre clé :

1. Rendez-vous sur le portail développeur Riot : https://developer.riotgames.com
2. Connectez-vous avec un compte Riot, les testeurs du PFE auront reçus des credentials d'un compte pour les testeurs via la newsletter envoyée le 11/06/2026
3. Sur le tableau de bord, générez une clé de développement (Development API Key). Cette clé commence par `RGAPI-` et expire après 24 heures. Pour un usage prolongé, il faut enregistrer un projet et demander une clé de production.
4. Renseignez la clé dans votre fichier `.env` :

```
RIOT_API_KEY=RGAPI-xxxxxxxx-xxxx-xxxx-xxxx-xxxxxxxxxxxx
```

### 5. Préparer la base de données

Le projet utilise MySQL. Créez une base de données (par exemple `Scrimly`), puis renseignez la connexion dans votre fichier `.env` :

```
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=Scrimly
DB_USERNAME=root
DB_PASSWORD=
```

```bash
php artisan migrate
```

#### Données de démonstration (seeder)

Le projet fournit un seeder de démonstration (`DemoDataSeeder`) qui crée des utilisateurs, des équipes, des scrims et des données associées pour disposer d'un environnement déjà rempli. Vous pouvez l'exécuter avec :

```bash
php artisan db:seed
```

Important : ce seeder peut être long à s'exécuter (plusieurs minutes). Lorsqu'une clé `RIOT_API_KEY` est configurée, il interroge réellement l'API Riot Games pour chaque compte de démonstration (profil, rang, parties récentes). Pour respecter les limites de la clé de développement Riot (20 requêtes par seconde et 100 requêtes toutes les 2 minutes), le seeder marque volontairement une pause entre chaque joueur (`sleep` de quelques secondes). Cette temporisation évite de dépasser les quotas et d'obtenir des erreurs de type « rate limit ».

Si aucune clé `RIOT_API_KEY` n'est définie, le seeder bascule automatiquement sur des données factices et s'exécute alors beaucoup plus rapidement.

Tous les comptes créés par le seeder utilisent le mot de passe `password`. Vous pouvez par exemple vous connecter avec le compte de test `testuser` (adresse `test@example.com`), ou avec l'un des comptes de démonstration dont l'adresse suit le format `pseudo@example.com`.

### 6. Configurer le temps réel (chat)

Le chat repose sur Laravel Reverb. Aucune installation supplémentaire n'est nécessaire : le paquet `laravel/reverb` (côté PHP) et les paquets `laravel-echo` et `pusher-js` (côté JavaScript) sont déjà déclarés dans le projet et installés lors de l'étape 2 (`composer install` et `npm install`). Le code (canaux, client Echo, écoute des évènements) est également déjà présent dans le dépôt.

Il reste seulement à renseigner les identifiants Reverb, qui sont absents par défaut pour des raisons de sécurité. Les variables sont déjà présentes dans `.env.example` (donc dans votre `.env`), avec `BROADCAST_CONNECTION=reverb` et les identifiants laissés vides :

```
BROADCAST_CONNECTION=reverb

REVERB_APP_ID=
REVERB_APP_KEY=
REVERB_APP_SECRET=
REVERB_HOST="localhost"
REVERB_PORT=8080
REVERB_SCHEME=http

VITE_REVERB_APP_KEY="${REVERB_APP_KEY}"
VITE_REVERB_HOST="${REVERB_HOST}"
VITE_REVERB_PORT="${REVERB_PORT}"
VITE_REVERB_SCHEME="${REVERB_SCHEME}"
```

Générez automatiquement les identifiants `REVERB_APP_ID`, `REVERB_APP_KEY` et `REVERB_APP_SECRET` avec :

```bash
php artisan reverb:install
```

Vous pouvez aussi les renseigner manuellement (n'importe quelles valeurs cohérentes en local, `VITE_REVERB_APP_KEY` devant correspondre à `REVERB_APP_KEY`). Démarrez ensuite le serveur de websockets :

```bash
php artisan reverb:start
```

### 7. Activer la tâche planifiée

L'application repose sur une tâche planifiée qui fait passer automatiquement les scrims au statut « en cours » lorsque leur date et heure prévues sont atteintes (commande `app:start-scrims`, exécutée chaque minute).

En développement, lancez le planificateur dans un terminal dédié :

```bash
php artisan schedule:work
```

### 8. Lancer l'application en développement

L'environnement de développement complet (serveur, file d'attente, logs et Vite) se lance avec :

```bash
composer run dev
```


### 9. Production

Pour générer les assets optimisés :

```bash
npm run build
```

