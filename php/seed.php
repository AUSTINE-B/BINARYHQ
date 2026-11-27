<?php
require_once __DIR__ . '/connect.php';
$pdo = campusnestDatabase();

function findOrCreateSeedUser(PDO $pdo, string $email, string $name, string $role): int
{
    $statement = $pdo->prepare('SELECT id FROM users WHERE email=:email');
    $statement->execute(['email'=>$email]);
    $id = $statement->fetchColumn();
    if ($id) return (int)$id;
    $statement = $pdo->prepare('INSERT INTO users(role,full_name,email,phone,password_hash,active,approved) VALUES(:role,:name,:email,:phone,:password,1,:approved)');
    $statement->execute([
        'role'=>$role,
        'name'=>$name,
        'email'=>$email,
        'phone'=>'+237600000000',
        'password'=>password_hash('Campus123!', PASSWORD_DEFAULT),
        'approved'=>$role === 'student' ? 1 : 1,
    ]);
    return (int)$pdo->lastInsertId();
}

$landlordId = findOrCreateSeedUser($pdo, 'demo.landlord@campusnest.cm', 'CampusNest Demo Landlord', 'landlord');
$employerId = findOrCreateSeedUser($pdo, 'demo.employer@campusnest.cm', 'CampusNest Demo Employer', 'employer');

$imageFiles = glob(__DIR__ . '/../assets/images/*.jpg') ?: [];
sort($imageFiles, SORT_NATURAL | SORT_FLAG_CASE);

$names = [
    'Palm Court Hostel','Hilltop Studios','Green Gate Residence','Modern City Apartments','Sunset View Residence',
    'Lakefront Student Lodge','Campus Square Apartments','Silver Gate Homes','Palm Ridge Studios','Blue Sky Residence',
    'Westline Apartments','Mile 17 Student Homes','Bonaberi View Lodge','Molyko Heights','Buea Central Studios',
    'Claret Court','Unity Residence','Rosewood Apartments','Garden View Homes','Atlantic Student Flats',
    'Fako Heights','Towerside Residence','Greenleaf Apartments','Highland Lodge','Cedar Court',
    'Golden Gate Flats','Orchid Residence','Metro Campus Homes','Mountain View Apartments'
];

$neighborhoods = ['Molyko','Bonabuma','Mile 17','Clerks Quarters','Great Soppo','Likoko','Bokwango','Bova','Mile 16','Malingo'];
$types = ['Hostel','Studio','Apartment','Single room'];

foreach ($imageFiles as $index => $imagePath) {
    $name = $names[$index] ?? ('CampusNest Residence ' . ($index + 1));
    $neighborhood = $neighborhoods[$index % count($neighborhoods)];
    $type = $types[$index % count($types)];
    $price = 25000 + (($index * 3500) % 45000);
    $institution = $index % 3 === 0 ? 'RHIBMS' : 'University of Buea';
    $photoUrl = 'assets/images/' . basename($imagePath);

    $statement = $pdo->prepare('SELECT id FROM listings WHERE title=:title');
    $statement->execute(['title' => $name]);

    if ($statement->fetch()) {
        $update = $pdo->prepare("UPDATE listings SET landlord_id=:landlord,institution=:institution,city='Buea',neighborhood=:neighborhood,listing_type=:type,price=:price,description='Comfortable student accommodation in Buea.',furnished=1,available=1,latitude=:latitude,longitude=:longitude,photo_url=:photo,status='approved' WHERE title=:title");
        $update->execute([
            'landlord'=>$landlordId,'institution'=>$institution,'neighborhood'=>$neighborhood,'type'=>$type,
            'price'=>$price,'latitude'=>4.1518 + ($index * 0.0007),'longitude'=>9.2453 + ($index * 0.0006),
            'photo'=>$photoUrl,'title'=>$name,
        ]);
        continue;
    }

    $insert = $pdo->prepare("INSERT INTO listings(landlord_id,title,institution,city,neighborhood,listing_type,price,description,furnished,available,latitude,longitude,photo_url,status) VALUES(:landlord,:title,:institution,'Buea',:neighborhood,:type,:price,'Comfortable student accommodation in Buea.',1,1,:latitude,:longitude,:photo,'approved')");
    $insert->execute([
        'landlord'=>$landlordId,'title'=>$name,'institution'=>$institution,'neighborhood'=>$neighborhood,
        'type'=>$type,'price'=>$price,'latitude'=>4.1518 + ($index * 0.0007),
        'longitude'=>9.2453 + ($index * 0.0006),'photo'=>$photoUrl,
    ]);
}

$jobs = [
    ['Campus Ambassador','Promote student programs and events around campus.','Buea','Part-time','Good communication skills','15 000 FCFA / month','Apply through CampusNest'],
    ['Library Assistant','Support daily library operations and student requests.','Buea','Internship','Attention to detail','Allowance available','Submit application through CampusNest'],
];
foreach ($jobs as $job) {
    $statement=$pdo->prepare('SELECT id FROM jobs WHERE title=:title'); $statement->execute(['title'=>$job[0]]);
    if ($statement->fetch()) continue;
    $insert=$pdo->prepare("INSERT INTO jobs(employer_id,title,description,location,job_type,requirements,compensation,application_instructions,status) VALUES(:employer,:title,:description,:location,:type,:requirements,:compensation,:instructions,'approved')");
    $insert->execute(['employer'=>$employerId,'title'=>$job[0],'description'=>$job[1],'location'=>$job[2],'type'=>$job[3],'requirements'=>$job[4],'compensation'=>$job[5],'instructions'=>$job[6]]);
}

echo 'CampusNest demo records are ready.';
