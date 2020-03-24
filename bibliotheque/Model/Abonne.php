<?php


namespace Model;


use App\Cnx;

/**
 * Class Abonne
 * @package Model
 */
class Abonne
{
    /**
     * @var int|null
     */
    private $id;

    /**
     * @var string|null
     */
    private $prenom;

    /**
     * @return int|null
     */
    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * @param int|null $id
     * @return Abonne
     */
    public function setId(?int $id): Abonne
    {
        $this->id = $id;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getPrenom(): ?string
    {
        return $this->prenom;
    }

    /**
     * @param string|null $prenom
     * @return Abonne
     */
    public function setPrenom(?string $prenom): Abonne
    {
        $this->prenom = $prenom;

        return $this;
    }

    public function save()
    {
        if (empty($this->id)) {
            $this->insert();
        } else {
            $this->update();
        }
    }

    public function delete()
    {
        $pdo = Cnx::getInstance();

        $query = 'DELETE FROM abonne WHERE id = ' . $this->id;
        $pdo->exec($query);
    }

    /**
     * @param array $errors le tableau qui va contenir les erreurs, passé par référence
     * @return bool valide ou invalide
     */
    public function validate(array &$errors): bool
    {
        if (empty($this->prenom)) {
            $errors[] = 'Le prénom est obligatoire';
        } elseif (mb_strlen($this->prenom) > 20) {
            $errors[] = 'Le prénom ne doit pas faire plus de 20 caractères';
        }

        return empty($errors);
    }

    public function hasEmprunts(): bool
    {
        $pdo = Cnx::getInstance();

        $query = 'SELECT count(*) FROM emprunt WHERE id_abonne = ' . $this->id;
        $stmt = $pdo->query($query);
        $count = $stmt->fetchColumn();

        return $count != 0;
    }

    private function insert()
    {
        $pdo = Cnx::getInstance();

        $query = 'INSERT INTO abonne (prenom) VALUES (:prenom)';
        $stmt  = $pdo->prepare($query);
        $stmt->execute([
            // bind value avec la valeur de l'attribut prenom de l'objet
            ':prenom' => $this->prenom
        ]);
    }

    private function update()
    {
        $pdo = Cnx::getInstance();

        $query = 'UPDATE abonne SET prenom = :prenom WHERE id = :id';
        $stmt  = $pdo->prepare($query);
        $stmt->execute([
            ':prenom' => $this->prenom,
            ':id' => $this->id
        ]);
    }

    /**
     * @return Abonne[]
     */
    public static function findAll(): array
    {
        $pdo = Cnx::getInstance();

        // retourner un tableau d'objets Abonne au lieu du tableau multidimentionnel
        // que retourne le fetchAll() de PDO
        // et l'utiliser dans abonnes.php pour lister les abonnés dans un tableau HTML

        $stmt = $pdo->query('SELECT * FROM abonne ORDER BY id');
        $result = $stmt->fetchAll();

        $abonnes = [];

        foreach ($result as $data) {
            $abonne = self::createFromDb($data);

            $abonnes[] = $abonne;
        }

        return $abonnes;
    }

    public static function find(int $id): ?self
    {
        $pdo = Cnx::getInstance();

        $query = 'SELECT * FROM abonne WHERE id = ' . $id;
        $stmt = $pdo->query($query);
        $data = $stmt->fetch();

        if (!empty($data)) {
            return self::createFromDb($data);
        }
    }

    private static function createFromDb(array $data): self
    {
        $abonne = new self(); // $abonne = new Abonne();

        // on sette ses attributs avec les données venant de la bdd
        $abonne
            ->setId($data['id'])
            ->setPrenom($data['prenom'])
        ;

        return $abonne;
    }
}
