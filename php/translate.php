<?php
header('Content-Type: application/json; charset=utf-8');

$text = trim((string)($_POST['text'] ?? $_GET['text'] ?? ''));
$source = strtolower(trim((string)($_POST['source'] ?? $_GET['source'] ?? 'auto')));
$target = strtolower(trim((string)($_POST['target'] ?? $_GET['target'] ?? '')));

if ($text === '') {
    http_response_code(400);
    echo json_encode(['error' => 'Enter text to translate.']);
    exit;
}
if (strlen($text) > 5000) {
    http_response_code(400);
    echo json_encode(['error' => 'Text must not exceed 5,000 characters.']);
    exit;
}

function campusnestDetectLanguage(string $text): string
{
    $lower = function_exists('mb_strtolower') ? mb_strtolower($text, 'UTF-8') : strtolower($text);
    if (preg_match('/[àâçéèêëîïôùûüÿœæ]/u', $lower)) {
        return 'fr';
    }

    $frenchWords = ['bonjour','bonsoir','merci','oui','non','je','tu','nous','vous','avec','pour','dans','maison','chambre','étudiant','étudiante','recherche','prix','location','buea','yaounde','douala'];
    $englishWords = ['hello','please','thank','yes','no','i','you','we','with','for','in','house','room','student','search','price','location','buea','yaounde','douala'];
    $words = preg_split('/[^[:alpha:]À-ÿ]+/u', $lower, -1, PREG_SPLIT_NO_EMPTY) ?: [];
    $frScore = count(array_intersect($words, $frenchWords));
    $enScore = count(array_intersect($words, $englishWords));
    return $frScore > $enScore ? 'fr' : 'en';
}

$dictionary = [
    'hello' => 'bonjour',
    'hi' => 'salut',
    'good morning' => 'bonjour',
    'good evening' => 'bonsoir',
    'goodbye' => 'au revoir',
    'please' => 's’il vous plaît',
    'thank you' => 'merci',
    'thanks' => 'merci',
    'yes' => 'oui',
    'no' => 'non',
    'sorry' => 'désolé',
    'excuse me' => 'excusez-moi',
    'how are you' => 'comment allez-vous',
    'i am fine' => 'je vais bien',
    'welcome' => 'bienvenue',
    'room' => 'chambre',
    'rooms' => 'chambres',
    'house' => 'maison',
    'apartment' => 'appartement',
    'studio' => 'studio',
    'hostel' => 'auberge',
    'hostel accommodation' => 'hébergement en auberge',
    'rent' => 'loyer',
    'monthly rent' => 'loyer mensuel',
    'price' => 'prix',
    'available' => 'disponible',
    'availability' => 'disponibilité',
    'city' => 'ville',
    'neighborhood' => 'quartier',
    'neighbourhood' => 'quartier',
    'location' => 'emplacement',
    'address' => 'adresse',
    'institution' => 'établissement',
    'school' => 'école',
    'university' => 'université',
    'student' => 'étudiant',
    'students' => 'étudiants',
    'tenant' => 'locataire',
    'landlord' => 'propriétaire',
    'payment' => 'paiement',
    'payments' => 'paiements',
    'book' => 'réserver',
    'booking' => 'réservation',
    'search' => 'rechercher',
    'find' => 'trouver',
    'view' => 'voir',
    'open' => 'ouvrir',
    'close' => 'fermer',
    'send' => 'envoyer',
    'submit' => 'soumettre',
    'save' => 'enregistrer',
    'cancel' => 'annuler',
    'confirm' => 'confirmer',
    'approve' => 'approuver',
    'reject' => 'rejeter',
    'approved' => 'approuvé',
    'pending' => 'en attente',
    'active' => 'actif',
    'inactive' => 'inactif',
    'account' => 'compte',
    'login' => 'connexion',
    'log in' => 'se connecter',
    'sign up' => 's’inscrire',
    'sign out' => 'se déconnecter',
    'password' => 'mot de passe',
    'email' => 'e-mail',
    'phone' => 'téléphone',
    'name' => 'nom',
    'full name' => 'nom complet',
    'message' => 'message',
    'messages' => 'messages',
    'notification' => 'notification',
    'notifications' => 'notifications',
    'dashboard' => 'tableau de bord',
    'services' => 'services',
    'service' => 'service',
    'repair' => 'réparation',
    'repairs' => 'réparations',
    'cleaning' => 'nettoyage',
    'job' => 'emploi',
    'jobs' => 'emplois',
    'application' => 'candidature',
    'applications' => 'candidatures',
    'employer' => 'employeur',
    'date' => 'date',
    'time' => 'heure',
    'today' => 'aujourd’hui',
    'tomorrow' => 'demain',
    'month' => 'mois',
    'year' => 'année',
    'description' => 'description',
    'details' => 'détails',
    'category' => 'catégorie',
    'problem' => 'problème',
    'help' => 'aide',
    'privacy' => 'confidentialité',
    'terms' => 'conditions',
    'contact' => 'contacter',
    'contact us' => 'contactez-nous',
    'photo' => 'photo',
    'photos' => 'photos',
    'furnished' => 'meublé',
    'internet' => 'internet',
    'water' => 'eau',
    'electricity' => 'électricité',
    'security' => 'sécurité',
    'parking' => 'parking',
    'bathroom' => 'salle de bain',
    'kitchen' => 'cuisine',
    'bedroom' => 'chambre',
    'bed' => 'lit',
    'door' => 'porte',
    'window' => 'fenêtre',
    'morning' => 'matin',
    'afternoon' => 'après-midi',
    'evening' => 'soir',
    'night' => 'nuit',
    'where' => 'où',
    'how' => 'comment',
    'what' => 'quoi',
    'when' => 'quand',
    'who' => 'qui',
    'why' => 'pourquoi',
    'from' => 'de',
    'to' => 'à',
    'for' => 'pour',
    'and' => 'et',
    'or' => 'ou',
    'with' => 'avec',
    'without' => 'sans',
    'in' => 'dans',
    'at' => 'à',
    'of' => 'de',
    'the' => 'le',
    'a' => 'un',
    'an' => 'un',
    'my' => 'mon',
    'your' => 'votre',
    'our' => 'notre',
    'this' => 'ce',
    'that' => 'cela',
    'good' => 'bon',
    'bad' => 'mauvais',
    'new' => 'nouveau',
    'old' => 'ancien',
    'small' => 'petit',
    'big' => 'grand',
    'more' => 'plus',
    'less' => 'moins',
    'near' => 'près',
    'far' => 'loin',
];

$reverse = [];
foreach ($dictionary as $english => $french) {
    if (!isset($reverse[$french])) {
        $reverse[$french] = $english;
    }
}
$translationMap = $source === 'fr' ? $reverse : $dictionary;

if ($source === 'auto') {
    $source = campusnestDetectLanguage($text);
}
if (!in_array($source, ['en', 'fr'], true)) {
    $source = campusnestDetectLanguage($text);
}
if ($target === '' || $target === 'auto') {
    $target = $source === 'fr' ? 'en' : 'fr';
}
if (!in_array($target, ['en', 'fr'], true) || $target === $source) {
    $target = $source === 'fr' ? 'en' : 'fr';
}
$translationMap = $target === 'fr' ? $dictionary : $reverse;

// Translate longer expressions before individual words to preserve natural phrases.
uksort($translationMap, static function ($a, $b): int {
    return strlen($b) <=> strlen($a);
});
$translation = $text;
foreach ($translationMap as $from => $to) {
    $escaped = preg_quote($from, '/');
    $translation = preg_replace('/\b' . $escaped . '\b/iu', $to, $translation);
    if ($translation === null) {
        $translation = $text;
        break;
    }
}

echo json_encode([
    'translation' => $translation,
    'source' => $source,
    'target' => $target,
], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
