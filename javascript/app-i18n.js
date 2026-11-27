(function () {
  'use strict';

  const translations = {
    'CampusNest': 'CampusNest',
    'Rooms': 'Logements',
    'Map': 'Carte',
    'Student Jobs': 'Emplois étudiants',
    'Services': 'Services',
    'Repairs': 'Réparations',
    'Cleaning': 'Nettoyage',
    'Log in': 'Connexion',
    'Please log in to view your dashboard.': 'Veuillez vous connecter pour consulter votre tableau de bord.',
    'Log in - CampusNest': 'Connexion - CampusNest',
    'Sign up': 'S’inscrire',
    'Create your account': 'Créez votre compte',
    'Landlord or provider': 'Propriétaire ou prestataire',
    'Cleaner or company': 'Agent de nettoyage ou entreprise',
    '1. Choose your account type': '1. Choisissez le type de compte',
    '2. Your details': '2. Vos informations',
    'Phone number': 'Numéro de téléphone',
    'At least 8 characters.': 'Au moins 8 caractères.',
    'I accept the terms and privacy notice': 'J’accepte les conditions et la politique de confidentialité',
    'I already have an account': 'J’ai déjà un compte',
    'Passwords do not match.': 'Les mots de passe ne correspondent pas.',
    'Back to log in': 'Retour à la connexion',
    'Reset password - CampusNest': 'Réinitialiser le mot de passe - CampusNest',
    'Student services - CampusNest': 'Services aux étudiants - CampusNest',
    'Student Jobs - CampusNest': 'Emplois étudiants - CampusNest',
    'English-French Translator - CampusNest': 'Traducteur anglais-français - CampusNest',
    'Rooms - CampusNest': 'Logements - CampusNest',
    'Dashboard - CampusNest': 'Tableau de bord - CampusNest',
    'Administration - CampusNest': 'Administration - CampusNest',
    'Dashboard': 'Tableau de bord',
    'Sign in': 'Connexion',
    'Sign out': 'Déconnexion',
    'Register': 'S’inscrire',
    'Home': 'Accueil',
    'Find a student room near your school': 'Trouvez une chambre étudiante près de votre école',
    'Search by institution, city, neighbourhood or keyword. All prices in FCFA.': 'Recherchez par établissement, ville, quartier ou mot-clé. Tous les prix sont en FCFA.',
    'Hostels near RHIBMS': 'Auberges près de RHIBMS',
    'Search for student rooms': 'Rechercher des logements étudiants',
    'Hostel': 'Auberge',
    'Single room': 'Chambre individuelle',
    'Studio': 'Studio',
    'Apartment': 'Appartement',
    'Recently added': 'Ajouts récents',
    'View': 'Voir',
    'Student services': 'Services aux étudiants',
    'Report a repair': 'Signaler une réparation',
    'Your landlord approves it before a repairer is sent.': 'Votre propriétaire doit approuver la demande avant l’envoi d’un réparateur.',
    'Book a cleaning': 'Réserver un nettoyage',
    'Pick a date and service type. Approved cleaners only.': 'Choisissez une date et un type de service. Seuls les agents approuvés interviennent.',
    'Find a student job': 'Trouver un emploi étudiant',
    'Landlord, repairer, cleaner or employer?': 'Propriétaire, réparateur, agent de nettoyage ou employeur ?',
    'Create an account for your role.': 'Créez un compte correspondant à votre rôle.',
    'Find accommodation': 'Trouver un logement',
    'Institution, city or keyword': 'Établissement, ville ou mot-clé',
    'City': 'Ville',
    'All types': 'Tous les types',
    'Max FCFA': 'Budget maximum (FCFA)',
    'Search': 'Rechercher',
    'No matching accommodation was found.': 'Aucun logement correspondant trouvé.',
    'Map locations appear where valid coordinates are available': 'Les logements apparaissent sur la carte lorsque leurs coordonnées sont disponibles.',
    'Contact the listing owner for more information.': 'Contactez le propriétaire pour plus d’informations.',
    'Cover photo': 'Photo de couverture',
    'Front elevation': 'Vue de façade',
    'Aerial / top view': 'Vue aérienne',
    'Left exterior view': 'Vue extérieure gauche',
    'Right exterior view': 'Vue extérieure droite',
    'Cover and front elevation': 'Couverture et vue de façade',
    'Choose this house': 'Choisir ce logement',
    'Close': 'Fermer',
    'This house is no longer available.': 'Ce logement n’est plus disponible.',
    'You already have a current house. Contact the admin to change it.': 'Vous avez déjà un logement. Contactez l’administration pour le modifier.',
    'Only student accounts can choose a house.': 'Seuls les comptes étudiants peuvent choisir un logement.',
    'Only tenants can submit a payment.': 'Seuls les locataires peuvent soumettre un paiement.',
    'Choose a house before making a payment.': 'Choisissez un logement avant d’effectuer un paiement.',
    'Current tenancy not found.': 'Location active introuvable.',
    'Pending payment not found.': 'Paiement en attente introuvable.',
    'This payment has already been processed.': 'Ce paiement a déjà été traité.',
    'Conversation not found.': 'Conversation introuvable.',
    'House conversation not found.': 'Conversation sur ce logement introuvable.',
    'Enter a valid payment type, amount, and month.': 'Saisissez un type de paiement, un montant et un mois valides.',
    'Choose a valid house.': 'Choisissez un logement valide.',
    'Unable to select this house.': 'Impossible de choisir ce logement.',
    'Student jobs': 'Emplois étudiants',
    'Browse CampusNest employer posts and ASTRA vacancies. ASTRA applications follow the instructions in each posting.': 'Consultez les offres des employeurs CampusNest et d’ASTRA. Pour postuler à une offre ASTRA, suivez les instructions de l’annonce.',
    'Browse the TechConnect service catalog. To place and track a booking, tenants sign in separately on ASTRA.': 'Consultez le catalogue de services TechConnect. Pour réserver et suivre une réservation, les locataires doivent se connecter à ASTRA.',
    'Open ASTRA to book': 'Ouvrir ASTRA pour réserver',
    'Loading ASTRA services…': 'Chargement des services ASTRA…',
    'CampusNest requests': 'Demandes CampusNest',
    'Repair request': 'Demande de réparation',
    'Cleaning request': 'Demande de nettoyage',
    'Property ID': 'Identifiant du logement',
    'Problem category': 'Catégorie du problème',
    'Choose category': 'Choisir une catégorie',
    'Plumbing': 'Plomberie',
    'Furniture': 'Mobilier',
    'Other': 'Autre',
    'Problem description': 'Description du problème',
    'Photo URL (optional)': 'Lien de la photo (facultatif)',
    'Submit repair request': 'Envoyer la demande de réparation',
    'Preferred date': 'Date souhaitée',
    'Service type': 'Type de service',
    'One-time cleaning': 'Nettoyage ponctuel',
    'Deep cleaning': 'Nettoyage en profondeur',
    'Moving cleaning': 'Nettoyage de déménagement',
    'Notes': 'Remarques',
    'Request cleaning': 'Demander un nettoyage',
    'Request submitted successfully.': 'Demande envoyée.',
    'Unable to submit request.': 'Impossible d’envoyer la demande.',
    'Keyword': 'Mot-clé',
    'Location': 'Lieu',
    'Part-time': 'Temps partiel',
    'Internship': 'Stage',
    'Remote': 'À distance',
    'Weekend': 'Week-end',
    'Loading jobs…': 'Chargement des offres…',
    'No jobs found.': 'Aucun emploi trouvé.',
    'Untitled job': 'Offre sans titre',
    'Open application link': 'Ouvrir le lien de candidature',
    'Application link is unavailable.': 'Le lien de candidature est indisponible.',
    'No matching student jobs were found.': 'Aucune offre d’emploi étudiant correspondante.',
    'Requirements:': 'Exigences :',
    'How to apply:': 'Comment postuler :',
    'Closing date:': 'Date limite :',
    'Log in to apply': 'Connectez-vous pour postuler',
    'Apply': 'Postuler',
    'English - French Translator': 'Traducteur anglais-français',
    'Translate text in both directions.': 'Traduisez du texte dans les deux sens.',
    'Detect language': 'Détecter la langue',
    'English': 'Anglais',
    'French': 'Français',
    'Source language': 'Langue source',
    'Translation language': 'Langue de traduction',
    'Swap languages': 'Inverser les langues',
    'Text': 'Texte',
    'Translation': 'Traduction',
    'Type or paste English or French text here…': 'Saisissez ou collez votre texte en anglais ou en français ici…',
    'Clear': 'Effacer',
    'Copy': 'Copier',
    'Translate': 'Traduire',
    'Translation will appear here.': 'La traduction apparaîtra ici.',
    'Copied to clipboard.': 'Texte copié.',
    'Could not copy automatically.': 'Copie automatique impossible.',
    'Unable to translate text.': 'Impossible de traduire le texte.',
    'Translation unavailable.': 'Traduction indisponible.',
    'Administration': 'Administration',
    'Primary navigation': 'Navigation principale',
    'Dashboard navigation': 'Navigation du tableau de bord',
    'Administration navigation': 'Navigation de l’administration',
    'Text translator': 'Traducteur de texte',
    'Administrator': 'Administrateur',
    'Platform management': 'Gestion de la plateforme',
    'Users': 'Utilisateurs',
    'Listings': 'Annonces',
    'Jobs': 'Emplois',
    'Overview': 'Vue d’ensemble',
    'My dashboard': 'Mon tableau de bord',
    'Tenant dashboard': 'Tableau de bord locataire',
    'Landlord dashboard': 'Tableau de bord propriétaire',
    'Repairer dashboard': 'Tableau de bord réparateur',
    'Cleaner dashboard': 'Tableau de bord agent de nettoyage',
    'Employer dashboard': 'Tableau de bord employeur',
    'My account': 'Mon compte',
    'No records.': 'Aucun enregistrement.',
    'Actions': 'Actions',
    'full name': 'nom complet',
    'landlord decision': 'décision du propriétaire',
    'active': 'actif',
    'approved': 'approuvé',
    'rejected': 'refusé',
    'pending': 'en attente',
    'paid': 'payé',
    'title': 'titre',
    'city': 'ville',
    'price': 'prix',
    'location': 'lieu',
    'status': 'statut',
    'role': 'rôle',
    'email': 'e-mail',
    'Notifications': 'Notifications',
    'Welcome back,': 'Bienvenue,',
    'Here is your CampusNest activity.': 'Voici votre activité sur CampusNest.',
    'Quick actions': 'Actions rapides',
    'Recent updates': 'Mises à jour récentes',
    'You are all caught up.': 'Vous êtes à jour.',
    'Not selected': 'Non sélectionné',
    'Selected': 'Sélectionné',
    'Pending payments': 'Paiements en attente',
    'Unread updates': 'Mises à jour non lues',
    'Houses': 'Logements',
    'Current tenants': 'Locataires actuels',
    'Payments to confirm': 'Paiements à confirmer',
    'Accounts': 'Comptes',
    'Payments': 'Paiements',
    'Assigned work': 'Travail attribué',
    'Role': 'Rôle',
    'student': 'étudiant',
    'landlord': 'propriétaire',
    'repairer': 'réparateur',
    'cleaner': 'agent de nettoyage',
    'employer': 'employeur',
    'admin': 'administrateur',
    'Browse houses': 'Parcourir les logements',
    'Current house': 'Logement actuel',
    'Pay rent or bills': 'Payer le loyer ou les factures',
    'Contact admin': 'Contacter l’administration',
    'Call a service': 'Appeler un prestataire',
    'Browse jobs': 'Parcourir les emplois',
    'Request repair': 'Demander une réparation',
    'Book cleaning': 'Réserver un nettoyage',
    'Applications': 'Candidatures',
    'Manage houses': 'Gérer les logements',
    'View tenants': 'Voir les locataires',
    'Confirm rent': 'Confirmer un loyer',
    'Review repairs': 'Examiner les réparations',
    'Manage accounts': 'Gérer les comptes',
    'Add services': 'Ajouter des services',
    'Message tenants': 'Écrire aux locataires',
    'Manage jobs': 'Gérer les emplois',
    'Review activity': 'Consulter l’activité',
    'Post a job': 'Publier une offre',
    'Review applications': 'Examiner les candidatures',
    'View assigned jobs': 'Voir les tâches attribuées',
    'Open service requests': 'Voir les demandes de service',
    'My houses': 'Mes logements',
    'Tenants': 'Locataires',
    'Collect rent': 'Encaisser le loyer',
    'Repair approvals': 'Approbation des réparations',
    'View houses': 'Voir les logements',
    'My house': 'Mon logement',
    'Repair requests': 'Demandes de réparation',
    'Message admin': 'Écrire à l’administration',
    'Rate house': 'Évaluer le logement',
    'Find your next home': 'Trouvez votre prochain logement',
    'Browse approved accommodation and select an available house to make it your current home.': 'Parcourez les logements approuvés et choisissez un logement disponible.',
    'View available houses': 'Voir les logements disponibles',
    'You already have a current house.': 'Vous avez déjà un logement.',
    'Contact the admin if you need to change your selection.': 'Contactez l’administration pour modifier votre choix.',
    'Your current house': 'Votre logement actuel',
    'FCFA / month': 'FCFA / mois',
    'House view': 'Vue du logement',
    'Interior photo': 'Photo intérieure',
    'No active tenants.': 'Aucun locataire actif.',
    'No house selected yet.': 'Aucun logement sélectionné.',
    'Landlord:': 'Propriétaire :',
    'Record a rent or bill payment': 'Enregistrer un paiement de loyer ou de facture',
    'Submitting a payment records it as pending. Your landlord or admin must confirm receipt; this page does not process a bank or mobile-money transfer.': 'Le paiement sera enregistré comme étant en attente. Le propriétaire ou l’administration doit confirmer sa réception. Aucun transfert bancaire ou mobile n’est effectué ici.',
    'Payment type': 'Type de paiement',
    'Rent': 'Loyer',
    'Electricity': 'Électricité',
    'Water': 'Eau',
    'Amount (FCFA)': 'Montant (FCFA)',
    'Month': 'Mois',
    'Submit payment record': 'Enregistrer le paiement',
    'Record rent or bill collected': 'Enregistrer un loyer ou une facture encaissés',
    'Tenant and house': 'Locataire et logement',
    'Record collected payment': 'Enregistrer le paiement encaissé',
    'Your payment history': 'Historique de vos paiements',
    'All house payments': 'Tous les paiements des logements',
    'Tenant payments': 'Paiements des locataires',
    'No payment records yet.': 'Aucun paiement enregistré.',
    'Tenant': 'Locataire',
    'House / type': 'Logement / type',
    'Amount': 'Montant',
    'Status': 'Statut',
    'Available': 'Disponible',
    'Occupied': 'Occupé',
    'Unavailable': 'Indisponible',
    'Approved': 'Approuvé',
    'Rejected': 'Refusé',
    'Action': 'Action',
    'Confirm': 'Confirmer',
    'Reject': 'Refuser',
    'Save': 'Enregistrer',
    'Started': 'Début',
    'Update rent': 'Modifier le loyer',
    'No active tenants yet.': 'Aucun locataire actif.',
    'End tenancy': 'Terminer la location',
    'Services you can call': 'Prestataires à contacter',
    'Platform services': 'Services de la plateforme',
    'Service name': 'Nom du service',
    'Phone': 'Téléphone',
    'Description': 'Description',
    'Add service': 'Ajouter un service',
    'Remove': 'Supprimer',
    'Inactive': 'Inactif',
    'Contact admin for details': 'Contactez l’administration pour plus de détails',
    'No services have been added yet.': 'Aucun service n’a encore été ajouté.',
    'ASTRA home services': 'Services à domicile ASTRA',
    'Browse TechConnect’s service catalog and place or track bookings in ASTRA. ASTRA requires a separate account.': 'Consultez le catalogue TechConnect et gérez vos réservations sur ASTRA. Un compte ASTRA distinct est nécessaire.',
    'Browse ASTRA services': 'Parcourir les services ASTRA',
    'Your repair requests': 'Vos demandes de réparation',
    'No repair requests yet.': 'Aucune demande de réparation.',
    'Details': 'Détails',
    'Decision': 'Décision',
    'Approve': 'Approuver',
    'Reject': 'Refuser',
    'Assigned cleaning requests': 'Demandes de nettoyage attribuées',
    'Assigned repair jobs': 'Réparations attribuées',
    'No assigned jobs yet.': 'Aucune tâche attribuée.',
    'For service requests, use the service page to contact the requester or admin.': 'Pour les demandes de service, contactez le demandeur ou l’administration depuis la page des services.',
    'Open services': 'Ouvrir les services',
    'Your job applications': 'Vos candidatures',
    'Applications for your jobs': 'Candidatures à vos offres',
    'Applicant': 'Candidat',
    'Submitted': 'Soumis',
    'Submitted on': 'Soumis le',
    'No applications found.': 'Aucune candidature trouvée.',
    'Browse student jobs': 'Parcourir les emplois étudiants',
    'Add a house': 'Ajouter un logement',
    'House name': 'Nom du logement',
    'Neighborhood': 'Quartier',
    'Institution': 'Établissement',
    'House type': 'Type de logement',
    'Choose type': 'Choisir un type',
    'Monthly rent (FCFA)': 'Loyer mensuel (FCFA)',
    'Furnished': 'Meublé',
    'Cover / exterior photo URL (optional)': 'Lien de la photo de couverture / extérieur (facultatif)',
    'Interior photo URLs (one URL per line)': 'Liens des photos intérieures (un lien par ligne)',
    'Add up to 12 interior photos so students can preview the inside.': 'Ajoutez jusqu’à 12 photos intérieures pour permettre aux étudiants de découvrir le logement.',
    'Submit house for review': 'Soumettre le logement pour validation',
    'Your houses': 'Vos logements',
    'Interior photos': 'Photos intérieures',
    'Save photos': 'Enregistrer les photos',
    'No houses have been added.': 'Aucun logement ajouté.',
    'Add a house on behalf of a landlord': 'Ajouter un logement pour un propriétaire',
    'Choose landlord': 'Choisir un propriétaire',
    'Cover / exterior photo URL': 'Lien de la photo de couverture / extérieur',
    'Add up to 12 interior photos for the house preview.': 'Ajoutez jusqu’à 12 photos intérieures pour présenter le logement.',
    'Add and publish house': 'Ajouter et publier le logement',
    'All houses': 'Tous les logements',
    'No houses have been listed.': 'Aucun logement publié.',
    'Ask the admin about': 'Écrire à l’administration au sujet de',
    'No messages yet. Send a message to start the conversation.': 'Aucun message. Envoyez un message pour démarrer la conversation.',
    'Your message': 'Votre message',
    'Send to admin': 'Envoyer à l’administration',
    'Tenant conversations': 'Conversations avec les locataires',
    'Open': 'Ouvrir',
    'Conversation': 'Conversation',
    'Reply to tenant': 'Répondre au locataire',
    'Send reply': 'Envoyer la réponse',
    'Rate your house': 'Évaluer votre logement',
    'Choose a house before leaving a rating.': 'Choisissez un logement avant de laisser une évaluation.',
    'Rating': 'Évaluation',
    'Comment': 'Commentaire',
    'Save rating': 'Enregistrer l’évaluation',
    'Send an update to tenants': 'Envoyer une annonce aux locataires',
    'Announcement': 'Annonce',
    'Send update': 'Envoyer l’annonce',
    'Updates from CampusNest': 'Actualités de CampusNest',
    'Mark all read': 'Tout marquer comme lu',
    'Platform activity': 'Activité de la plateforme',
    'When': 'Date',
    'Actor': 'Auteur',
    'Target': 'Cible',
    'No activity recorded yet.': 'Aucune activité enregistrée.',
    'Post a student job': 'Publier une offre étudiante',
    'Job title': 'Intitulé du poste',
    'Job type': 'Type d’emploi',
    'Compensation': 'Rémunération',
    'Requirements': 'Exigences',
    'Application instructions': 'Instructions pour postuler',
    'Submit job for review': 'Soumettre l’offre pour validation',
    'Your job posts': 'Vos offres d’emploi',
    'No jobs posted yet.': 'Aucune offre publiée.',
    'Manage student jobs': 'Gérer les emplois étudiants',
    'No jobs have been posted.': 'Aucune offre d’emploi publiée.',
    'Landlords and tenants': 'Propriétaires et locataires',
    'Name': 'Nom',
    'Email / phone': 'E-mail / téléphone',
    'Email': 'E-mail',
    'Status': 'Statut',
    'Suspended': 'Suspendu',
    'Awaiting approval': 'En attente d’approbation',
    'Activate': 'Activer',
    'Suspend': 'Suspendre',
    'No accounts found.': 'Aucun compte trouvé.',
    'Send an update to tenants': 'Envoyer une annonce aux locataires',
    'Reset password': 'Réinitialiser le mot de passe',
    'Request reset': 'Demander la réinitialisation',
    'Password': 'Mot de passe',
    'Email address': 'Adresse e-mail',
    'Enter your email': 'Saisissez votre adresse e-mail',
    'Forgot password?': 'Mot de passe oublié ?',
    'Create an account': 'Créer un compte',
    'Sign in to your account': 'Connectez-vous à votre compte',
    'Email or phone': 'E-mail ou téléphone',
    'Remember me': 'Se souvenir de moi',
    'Create account': 'Créer un compte',
    'Full name': 'Nom complet',
    'Choose your role': 'Choisissez votre rôle',
    'Student': 'Étudiant',
    'Landlord': 'Propriétaire',
    'Repairer': 'Réparateur',
    'Cleaner': 'Agent de nettoyage',
    'Employer': 'Employeur',
    'Confirm password': 'Confirmer le mot de passe',
    'Already have an account?': 'Vous avez déjà un compte ?',
    'No account yet?': 'Pas encore de compte ?',
    'Please log in to continue.': 'Veuillez vous connecter pour continuer.',
    'The request could not be completed.': 'La demande n’a pas pu aboutir.',
    'The server returned an invalid response.': 'Le serveur a renvoyé une réponse invalide.',
    'Unable to load your dashboard.': 'Impossible de charger votre tableau de bord.',
    'This account has no configured dashboard.': 'Aucun tableau de bord n’est configuré pour ce compte.',
    'Unable to load jobs.': 'Impossible de charger les offres d’emploi.',
    'Changes saved.': 'Modifications enregistrées.',
    'Please enter a message of up to 4,000 characters.': 'Veuillez saisir un message de 4 000 caractères maximum.',
    'Select a house before starting a house conversation.': 'Choisissez un logement avant de démarrer une conversation.',
    'FCFA': 'FCFA',
    '/ month': '/ mois',
    'Molyko, Buea': 'Molyko, Buea'
  };

  const originalText = new WeakMap();
  const translatedText = new WeakMap();
  const originalAttributes = new WeakMap();
  const translatedAttributes = new WeakMap();
  const attributeNames = ['placeholder', 'aria-label', 'title', 'alt'];
  let language = 'en';
  let observer;

  function getSavedLanguage() {
    try {
      return localStorage.getItem('campusnest-language') === 'fr' ? 'fr' : 'en';
    } catch (error) {
      return 'en';
    }
  }

  function translateTextNode(node) {
    const current = node.nodeValue;
    if (!current || !current.trim()) return;

    let english = originalText.get(node);
    if (english !== undefined && current !== translatedText.get(node)) {
      english = current;
      originalText.set(node, english);
      translatedText.delete(node);
    } else if (english === undefined) {
      english = current;
    }

    if (language === 'en') {
      if (originalText.has(node) && current === translatedText.get(node)) {
        node.nodeValue = english;
      }
      return;
    }

    const key = english.trim().replace(/\s+/g, ' ');
    const greeting = key.match(/^Welcome back, (.+)\. Here is your CampusNest activity\.$/);
    const dynamicPhrase = [
      [/^Requirements: (.+)$/, 'Exigences : $1'],
      [/^How to apply: (.+)$/, 'Comment postuler : $1'],
      [/^Closing date: (.+)$/, 'Date limite : $1'],
      [/^ASTRA vacancies are temporarily unavailable\. (.+)$/, 'Les offres ASTRA sont temporairement indisponibles. $1']
    ].find(([pattern]) => pattern.test(key));
    const french = greeting
      ? 'Bienvenue, ' + greeting[1] + '. Voici votre activité sur CampusNest.'
      : dynamicPhrase
        ? key.replace(dynamicPhrase[0], dynamicPhrase[1])
        : translations[key];
    if (!french) return;
    originalText.set(node, english);
    const leading = english.match(/^\s*/)[0];
    const trailing = english.match(/\s*$/)[0];
    const value = leading + french + trailing;
    if (value !== current) {
      translatedText.set(node, value);
      node.nodeValue = value;
    }
  }

  function translateAttribute(element, attribute) {
    const current = element.getAttribute(attribute);
    if (current === null) return;

    let saved = originalAttributes.get(element);
    let translated = translatedAttributes.get(element);
    if (!saved) {
      saved = {};
      originalAttributes.set(element, saved);
    }
    if (!translated) {
      translated = {};
      translatedAttributes.set(element, translated);
    }
    if (Object.prototype.hasOwnProperty.call(saved, attribute) && current !== translated[attribute]) {
      saved[attribute] = current;
      delete translated[attribute];
    } else if (!Object.prototype.hasOwnProperty.call(saved, attribute)) {
      saved[attribute] = current;
    }

    if (language === 'en') {
      if (translated[attribute] === current) {
        element.setAttribute(attribute, saved[attribute]);
      }
      return;
    }
    const french = translations[saved[attribute].trim().replace(/\s+/g, ' ')];
    if (french) {
      translated[attribute] = french;
      if (current !== french) element.setAttribute(attribute, french);
    }
  }

  function translateElement(root) {
    if (root.nodeType === Node.TEXT_NODE) {
      translateTextNode(root);
      return;
    }
    if (root.nodeType !== Node.ELEMENT_NODE && root.nodeType !== Node.DOCUMENT_NODE) return;

    if (root.nodeType === Node.ELEMENT_NODE) {
      const element = root;
      if (element.matches('script,style,noscript,[contenteditable="true"]')) return;
      if (element.matches('.language-toggle')) {
        updateToggle(element);
      } else {
        attributeNames.forEach(attribute => translateAttribute(element, attribute));
      }
    }

    const walker = document.createTreeWalker(root, NodeFilter.SHOW_TEXT, {
      acceptNode(node) {
        return node.parentElement && node.parentElement.closest('script,style,noscript,textarea,[contenteditable="true"],.language-toggle')
          ? NodeFilter.FILTER_REJECT
          : NodeFilter.FILTER_ACCEPT;
      }
    });
    const nodes = [];
    while (walker.nextNode()) nodes.push(walker.currentNode);
    nodes.forEach(translateTextNode);

    if (root.nodeType === Node.ELEMENT_NODE || root.nodeType === Node.DOCUMENT_NODE) {
      const selector = attributeNames.map(name => '[' + name + ']').join(',');
      const elements = Array.from(root.querySelectorAll(selector));
      if (root.nodeType === Node.ELEMENT_NODE && root.matches(selector)) elements.unshift(root);
      elements.forEach(element => {
        if (!element.matches('script,style,noscript')) {
          attributeNames.forEach(attribute => translateAttribute(element, attribute));
        }
      });
    }
  }

  function updateToggle(button) {
    button.textContent = language === 'en' ? 'FR' : 'EN';
    button.setAttribute('aria-label', language === 'en' ? 'Switch to French' : 'Passer en anglais');
    button.setAttribute('title', language === 'en' ? 'Switch to French' : 'Passer en anglais');
  }

  function addLanguageToggle() {
    const existing = document.querySelector('.language');
    let button = existing;
    if (existing) {
      button = document.createElement('button');
      button.type = 'button';
      button.className = existing.className;
      existing.replaceWith(button);
    } else {
      button = document.createElement('button');
      button.type = 'button';
      button.className = 'language-toggle';
      const container = document.querySelector('.top-actions,.actions,.inner,.topbar-inner');
      if (container) {
        container.prepend(button);
      } else {
        const header = document.querySelector('header');
        if (header) {
          button.className += ' language-toggle-floating';
          header.append(button);
        }
      }
    }
    if (button) {
      button.classList.add('language-toggle');
      button.addEventListener('click', toggleLanguage);
      updateToggle(button);
    }
  }

  function setLanguage(nextLanguage) {
    language = nextLanguage;
    document.documentElement.lang = language;
    try {
      localStorage.setItem('campusnest-language', language);
    } catch (error) {
      // The switch still works for the current page if storage is unavailable.
    }
    translateElement(document);
    document.querySelectorAll('.language-toggle').forEach(updateToggle);
  }

  function toggleLanguage() {
    setLanguage(language === 'en' ? 'fr' : 'en');
  }

  function start() {
    language = getSavedLanguage();
    addLanguageToggle();
    if (document.title) {
      const titleNode = document.querySelector('title').firstChild;
      if (titleNode) translateTextNode(titleNode);
    }
    translateElement(document);
    observer = new MutationObserver(records => {
      records.forEach(record => {
        if (record.type === 'characterData') {
          translateTextNode(record.target);
        } else {
          record.addedNodes.forEach(translateElement);
          if (record.type === 'attributes') translateAttribute(record.target, record.attributeName);
        }
      });
    });
    observer.observe(document.documentElement, {
      childList: true,
      subtree: true,
      characterData: true,
      attributes: true,
      attributeFilter: attributeNames
    });
  }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', start, {once: true});
  } else {
    start();
  }
})();
