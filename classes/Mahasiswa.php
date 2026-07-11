<?php
/**
 * Class Mahasiswa
 * Model untuk data mahasiswa (CRUD, pencarian, filter, pagination, statistik).
 */
class Mahasiswa
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::getInstance()->getConnection();
    }

    /** Ambil semua mahasiswa dengan filter + pagination */
    public function getAll(string $search = '', string $prodi = '', string $ipk = '', int $page = 1, int $limit = 10): array
    {
        [$sql, $params] = $this->buildFilter($search, $prodi, $ipk);

        // Hitung total baris untuk pagination
        $countSql  = str_replace("SELECT *", "SELECT COUNT(*)", $sql);
        $stmtCount = $this->db->prepare($countSql);
        $stmtCount->execute($params);
        $totalRows  = (int) $stmtCount->fetchColumn();
        $totalPages = (int) ceil($totalRows / $limit);

        $offset = ($page - 1) * $limit;
        $sql   .= " ORDER BY id DESC LIMIT $limit OFFSET $offset";
        $stmt   = $this->db->prepare($sql);
        $stmt->execute($params);

        return [
            'data'       => $stmt->fetchAll(),
            'totalRows'  => $totalRows,
            'totalPages' => $totalPages,
        ];
    }

    private function buildFilter(string $search, string $prodi, string $ipk): array
    {
        $sql    = "SELECT * FROM mahasiswa WHERE 1=1";
        $params = [];

        if ($search !== '') {
            $sql .= " AND (nim LIKE ? OR nama LIKE ?)";
            $params[] = "%$search%";
            $params[] = "%$search%";
        }
        if ($prodi !== '') {
            $sql .= " AND prodi = ?";
            $params[] = $prodi;
        }
        if ($ipk === '>=3')        $sql .= " AND ipk >= 3";
        elseif ($ipk === '2.5-3')  $sql .= " AND ipk BETWEEN 2.5 AND 2.99";
        elseif ($ipk === '<2.5')   $sql .= " AND ipk < 2.5";

        return [$sql, $params];
    }

    public function getById(int $id): ?array
    {
        $stmt = $this->db->prepare("SELECT * FROM mahasiswa WHERE id = ?");
        $stmt->execute([$id]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public function create(string $nim, string $nama, string $prodi, float $ipk, ?string $foto): bool
    {
        $stmt = $this->db->prepare(
            "INSERT INTO mahasiswa (nim, nama, prodi, ipk, foto) VALUES (?, ?, ?, ?, ?)"
        );
        return $stmt->execute([$nim, $nama, $prodi, $ipk, $foto]);
    }

    public function update(int $id, string $nim, string $nama, string $prodi, float $ipk, ?string $foto): bool
    {
        $stmt = $this->db->prepare(
            "UPDATE mahasiswa SET nim=?, nama=?, prodi=?, ipk=?, foto=? WHERE id=?"
        );
        return $stmt->execute([$nim, $nama, $prodi, $ipk, $foto, $id]);
    }

    public function delete(int $id): bool
    {
        $stmt = $this->db->prepare("DELETE FROM mahasiswa WHERE id=?");
        return $stmt->execute([$id]);
    }

    public function getListProdi(): array
    {
        return $this->db->query("SELECT DISTINCT prodi FROM mahasiswa")
                        ->fetchAll(PDO::FETCH_COLUMN);
    }

    /** Statistik untuk dashboard */
    public function getStats(): array
    {
        $total   = (int) $this->db->query("SELECT COUNT(*) FROM mahasiswa")->fetchColumn();
        $rataIPK = (float) $this->db->query("SELECT COALESCE(AVG(ipk),0) FROM mahasiswa")->fetchColumn();
        $lulus   = (int) $this->db->query("SELECT COUNT(*) FROM mahasiswa WHERE ipk >= 2.75")->fetchColumn();

        $prodiChart = [];
        $stmt = $this->db->query("SELECT prodi, COUNT(*) AS jml FROM mahasiswa GROUP BY prodi");
        while ($row = $stmt->fetch()) {
            $prodiChart[$row['prodi']] = (int) $row['jml'];
        }

        return [
            'total'      => $total,
            'rataIPK'    => $rataIPK,
            'lulus'      => $lulus,
            'tidakLulus' => $total - $lulus,
            'prodiChart' => $prodiChart,
        ];
    }

    /** Data untuk ekspor CSV (mengikuti filter) */
    public function getForExport(string $search = '', string $prodi = '', string $ipk = ''): array
    {
        [$sql, $params] = $this->buildFilter($search, $prodi, $ipk);
        $sql  = str_replace("SELECT *", "SELECT nim, nama, prodi, ipk", $sql);
        $stmt = $this->db->prepare($sql . " ORDER BY nama ASC");
        $stmt->execute($params);
        return $stmt->fetchAll();
    }
}
