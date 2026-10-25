# Automatisation Sonic Pro : demandes, rendez-vous et notifications

## Parcours en production

Le formulaire du site envoie les demandes à `support@sonicproelectricite.ca`. Le workflow n8n publié surveille cette boîte par IMAP. Une identité stable empêche de traiter deux fois le même message.

Les coordonnées sont extraites des champs du formulaire, avant le texte libre. L’IA lit le message complet en français ou en anglais et renvoie uniquement des faits validés : besoin électrique explicite, exclusions emploi/offre commerciale/injection, confiance, urgence et langue. La langue est d’abord estimée sur le seul texte du client ; le verdict IA sert de repli pour les cas ambigus. Une demande électrique claire et sûre est traitée automatiquement. Une candidature, une offre IT, une injection, un message incohérent ou une erreur IA passe par Telegram pour décision d’un administrateur vérifié. La catégorie métier unique reste `Intervention_Depuis_site_web` ; le statut de traitement est distinct.

Après approbation automatique ou humaine : contact CRM, lien de calendrier, accusé SMTP, enregistrement du résultat, archivage de la notification SNS, publication AWS et conservation du `MessageId`. L’urgence ne constitue pas une confirmation d’intervention.

L’accusé affiche d’abord la langue de la demande, puis l’autre langue. Si la langue est indéterminée, le français apparaît en premier. Chaque section présente un téléphone cliquable, son libellé et un lien de secours accessibles. Un GIF de combiné rouge avec ondes de sonnerie tourne en boucle. Il est hébergé sur le site Sonic Pro, sans service d’images tiers. Les clients courriel qui bloquent les animations affichent sa première image, déjà lisible comme un téléphone rouge qui sonne. Les tarifs figurent dans un tableau bilingue à trois colonnes : service, première heure et heures suivantes. Le calendrier Google, les montants existants et les coordonnées de soutien sont conservés.

## Contrôle des demandes et exclusions

Les contrôles indépendants portent sur le texte libre du client, sans utiliser le nom de Sonic Pro, l’objet du formulaire ou les coordonnées comme preuve d’un besoin électrique. Les liens externes dans le message, promesses de gains, offres de prestations, candidatures et injections évidentes exigent une revue Telegram. Un fournisseur qui propose des estimations électriques à Sonic Pro est une offre commerciale, même s’il emploie le mot « électrique ».

L’IA doit fournir un extrait exact du message client prouvant sa demande d’assistance. La validation vérifie cet extrait dans le texte reçu et refait les contrôles déterministes : une confiance élevée ou un booléen positif ne peuvent pas les contourner. Un contrôle négatif ou une preuve absente conduit à la revue, sans accusé automatique ni création CRM. L’administrateur vérifié conserve la décision Process/No-process. Les contrôles textuels sont conservateurs : certains clients légitimes, notamment ceux qui joignent des liens, peuvent donc nécessiter une validation humaine.

Un audit du 24 octobre a confirmé qu’un ancien verdict pouvait être contradictoire : champs positifs et confiance élevée, mais explication indiquant un contenu hors sujet. Les tests couvrent désormais les promotions de gains, les fournisseurs de métrés électriques, l’emploi et les injections, y compris lorsqu’un faux verdict IA tente de les accepter. Les tests vérifient aussi que les demandes électriques ordinaires FR/EN restent admissibles.

## Liens de calendrier et mesure des rendez-vous

Le téléphone ouvre `/calendar-api/email-call?t=<identifiant aléatoire>`, puis une redirection fixe vers le calendrier Google approuvé. Le lien contient un identifiant opaque de 64 caractères hexadécimaux, sans nom, courriel ni téléphone. Il est lié en base à la demande d’origine. Les redirections arbitraires sont impossibles : aucune URL de destination n’est acceptée du navigateur.

Le serveur enregistre un identifiant de clic et sa date, sans adresse IP ni navigateur dans l’enregistrement analytique. La protection anti-abus existante utilise séparément une empreinte d’adresse IP. Les requêtes `HEAD` ne sont pas comptées. Les scanners de sécurité des messageries peuvent effectuer des requêtes `GET` : **une ouverture du lien n’est donc ni une preuve de clic humain ni une réservation**.

Toutes les cinq minutes, un workflow distinct importe les clics et les événements Google Agenda modifiés au cours des huit derniers jours. L’export est protégé par une clé aléatoire : valeur privée sur Hostinger et identifiant chiffré dans n8n, limité au domaine `sonicproelectricite.ca`. Aucune clé n’est versionnée. Une importation répétée ne crée pas de double comptage.

Un rendez-vous est « associé à l’automatisation » si un participant possède le même courriel qu’une demande ayant reçu l’accusé, et si l’événement a été créé après une ouverture de son lien dans les 30 jours précédents. Le lien ouvert le plus récemment est retenu. Cette association est une **inférence par correspondance du courriel**, pas un identifiant de campagne renvoyé par Google. Un événement créé autrement avec le même client peut aussi correspondre ; un client qui réserve avec une autre adresse ne correspondra pas. Le rapport le dit explicitement. Les réservations sans correspondance restent non attribuées. Les annulations conservent l’attribution et mettent à jour le statut.

La fenêtre glissante de huit jours permet de rattraper une interruption courte. Après une interruption plus longue, un rattrapage contrôlé est nécessaire. Le rapport avertit si la dernière synchronisation réussie dépasse 15 minutes. Les statuts sont observés au moment du rapport et ne sont pas des instantanés historiques immuables.

## Courriels de confirmation de réservation

Le calendrier natif du site envoie l’invitation Google au client. Après confirmation par Google, un courriel distinct est envoyé via WordPress aux deux adresses fixes : `sonic.pro.electricite@gmail.com` et `support@sonicproelectricite.ca`. Il contient le nom, le courriel, le téléphone, l’horaire de Montréal et le lien Agenda. Une marque privée évite les doublons. L’échec d’un courriel ne doit pas recréer le rendez-vous.

Cette confirmation distincte concerne les réservations effectuées dans le calendrier natif du site. Les réservations faites directement sur la page Google sont synchronisées pour le reporting ; les notifications du propriétaire sur cette page restent gérées par Google. Cette automatisation n’instaure pas une approbation manuelle des créneaux.

## Boîte courriel : lu et Traité

Le synchroniseur sélectionne uniquement les messages aux statuts `processed` ou `suspicious` après décision finale, avec leur identifiant RFC `Message-ID`. Les demandes en attente Telegram ne sont pas sélectionnées. Pour chaque message, le serveur vérifie l’identifiant exact par IMAP, ajoute le drapeau `\\Seen`, puis le déplace dans `INBOX.Traite` — affiché comme **Traite** par le client de messagerie. Le message reste récupérable dans ce dossier.

Un nouvel essai recherche également le message dans ce dossier. Le déplacement utilise `UID MOVE`, ou `UID COPY` et `UID EXPUNGE` sélectif si le serveur ne propose que `UIDPLUS`. Aucun `EXPUNGE` global n’est utilisé. Après trois échecs, une inspection est nécessaire ; l’erreur et le nombre d’essais sont conservés en base. Le succès est enregistré séparément de l’envoi de l’accusé. L’intervalle habituel avant classement est de cinq minutes.

## Copies des accusés dans Envoyés (Hostinger)

Le SMTP ne crée pas automatiquement une copie dans la boîte IMAP. Après une acceptation SMTP, l’étape « Record successful reply » marque la demande comme traitée et insère atomiquement le destinataire validé, le sujet, le HTML exact, la réponse SMTP et son `Message-ID` dans `sonicpro_sent_email_copies`. Cette étape ne réexécute pas le SMTP.

L’archivage est relié directement au nœud **Hostinger Outgoing Email (SMTP)** dans **Sonic-Pro_Prod** (`3v0xFd3DTeK7T34W`) : SMTP accepté → enregistrement atomique du résultat et de la copie → contrôle de présence → copie IMAP → enregistrement du résultat → notification SNS. Il ne possède aucun déclencheur ni branche planifiée séparée. L’ancien workflow `3O8TCS9gjEEtu0C7` reste désactivé.

Le helper authentifié `/calendar-api/mailbox-sent` se connecte en IMAP TLS avec le credential privé existant. Il recherche le dossier portant le drapeau `\Sent` ou un dossier existant `INBOX.Sent` / `Sent` ; sur cette boîte le dossier vérifié est **INBOX.Sent**. Aucun dossier arbitraire n’est créé.

La copie conserve le destinataire, le sujet, le contenu HTML, la date d’envoi et le même `Message-ID` que le SMTP ; son enveloppe MIME est reconstruite pour l’archivage, et ne prétend pas reproduire les en-têtes de transport ajoutés en transit. Elle est marquée lue. Une recherche puis une vérification de l’en-tête exact empêchent les doublons ; un verrou par `Message-ID` sérialise les appels concurrents. Le succès n’est confirmé qu’après une nouvelle lecture IMAP du message et de son UID.

Le nœud HTTP d’archivage effectue jusqu’à trois essais, espacés de cinq secondes, sans réexécuter le SMTP. Après l’échec de ces essais, la copie passe à `failed` avec une erreur conservée en base, puis le workflow continue vers SNS. Aucune reprise planifiée n’est active. Pour corriger un échec persistant, inspecter l’exécution et réessayer uniquement l’étape de copie avec ses données d’origine. **Ne jamais relancer l’envoi SMTP pour corriger une copie manquante.** La colonne `attempts` désigne les passages de la branche, pas le nombre exact d’appels HTTP internes. Le destinataire, le HTML, le sujet et le Message-ID restent conservés dans `sonicpro_sent_email_copies`.

Le 24 octobre 2026, l’accusé de l’exécution **34626** a été repris depuis son HTML et sa preuve SMTP enregistrés, puis ajouté à `INBOX.Sent`. Un second archivage a retourné `already_present=true` avec le même UID, sans renvoi au client. Les autres envois historiques ne sont pas reconstruits automatiquement.

## Structure de la base PostgreSQL

| Table | Contenu principal | Clé / relation |
|---|---|---|
| `sonicpro_hostinger_messages` | Demande complète, coordonnées extraites, catégorie, état, décision, urgence, langue, verdict IA, accusé envoyé, contact CRM, classement IMAP et erreurs | PK `(workflow_id, mailbox, message_id)` ; identité source unique ; FK `contact_id` |
| `sonicpro_contacts` | Courriel unique, nom, téléphone, téléphone normalisé, adresse, première et dernière activité | PK `contact_id` ; courriel unique |
| `sonicpro_calendar_links` | Identifiant opaque du lien et demande d’origine | PK `token` ; une ligne par demande ; FK vers le message |
| `sonicpro_calendar_clicks` | Identifiant d’ouverture, lien, date de l’ouverture et date d’importation | PK `click_id` ; FK `token` |
| `sonicpro_calendar_bookings` | Identifiant Google, état, origine native/Google, dates de création/modification et du rendez-vous, courriels des participants, attribution et dernière synchronisation | PK `event_id` ; FK facultative `link_token` |
| `sonicpro_sent_email_copies` | Destinataire, sujet et HTML de l’accusé, Message-ID et réponse SMTP, date d’envoi, file/reprises d’archivage, dossier et UID IMAP | PK `archive_id` ; demande et Message-ID uniques ; FK vers le message |
| `sonicpro_sns_notifications` | **Copie exacte du sujet et du texte sortants**, sujet AWS, exécution, état, `aws_message_id`, date d’acceptation ou erreur | PK `notification_id` ; tentative unique par demande/exécution ; FK vers le message |
| `sonicpro_weekly_reports` | Période, clé hebdomadaire, état de publication, identifiant du message Telegram et date d’envoi | PK `report_key` |
| `sonicpro_integration_health` | Dernière synchronisation réussie du calendrier | PK `integration` |
| `sonicpro_email_review_messages` | Correspondance du message de demande avec le message publié dans le canal Telegram | Demande / identifiant Telegram exacts |

Les scripts de création sont additifs et réexécutables. Aucune table interne de n8n n’est modifiée. Les coordonnées, textes clients et archives SNS restent dans la base métier et les services nécessaires au traitement ; les exports versionnés ne contiennent pas de messages clients réels ni de mots de passe.

Un `MessageId` SNS signifie **acceptation de la publication par AWS**, pas réception par un abonné. Les abonnements et leur confirmation restent nécessaires. L’archive conserve les tentatives futures à partir du déploiement ; elle n’invente pas les notifications historiques.

## Rapport Telegram hebdomadaire

Lundi à **09:00 America/Toronto**, pour la semaine complète précédente, avec prise en compte des changements d’heure :

- demandes reçues, approuvées, urgentes, en attente, suspectes et échecs d’accusé ;
- nouveaux contacts CRM et total ;
- messages marqués lus/rangés et reste à ranger ;
- ouvertures du lien, demandes avec lien ouvert, réservations associées et annulations ;
- copies SNS archivées, publications acceptées, échecs et résultats en attente ;
- état de fraîcheur de la synchronisation du calendrier.

Seuls des agrégats sont publiés : aucun nom, téléphone, courriel ou texte client dans le résumé. La publication est revendiquée une seule fois par semaine. Le déclencheur manuel du rapport permet un aperçu sans envoi.

## Fichiers et exploitation

`n8n/main-workflow.json`, `n8n/calendar-sync-workflow.json` et `n8n/weekly-workflow.json` sont les exports sans données épinglées. Les identifiants de credentials dans `n8n/bindings.json` sont des références, pas leurs valeurs secrètes. Pour une autre instance, sélectionner ses propres credentials avant toute exécution.

`node n8n/build.cjs`, puis `node n8n/build-reporting.cjs` et `node n8n/build-sent.cjs` reconstruisent les exports à partir des sources. `node tests/test.cjs` vérifie l’ordre FR/EN, l’échappement des données, les exclusions IA, les liens, le graphe SNS et le rapport agrégé. Le test PostgreSQL vérifie la migration, le dédoublonnage, l’attribution et les annulations dans une base isolée. Les helpers PHP sont vérifiés avant déploiement ; le déploiement compare les versions distantes, conserve une sauvegarde et relit les fichiers installés.

Les scripts Hostinger sont des outils opérateur liés à l’environnement local ; les fichiers `secrets/`, jetons OAuth, clés d’export, sauvegardes et résultats d’exécution privés ne sont pas versionnés. Le correctif du formulaire renouvelle le nonce avant l’envoi et conserve consentement, piège anti-robot et limitation à cinq demandes par quinze minutes.

## Confirmation du formulaire de contact

Après un envoi accepté, la page Contact affiche une fenêtre de confirmation en haut de l’écran, avec une coche verte, le titre « Demande envoyée » / « Request sent », le message de réception et un bouton Fermer / Close. La fenêtre reste ouverte jusqu’à sa fermeture ; la touche Échap fonctionne et le bouton reçoit le focus. Une version sans JavaScript conserve le message en ligne. Le paramètre de résultat est retiré de l’URL après affichage pour éviter de présenter une ancienne confirmation après un rechargement. Le formulaire, le renouvellement du nonce et les vérifications serveur restent actifs. Ce résultat visuel ne constitue pas une preuve de livraison du courriel.
