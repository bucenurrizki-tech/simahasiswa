<?php
/**
 * Class Nilai
 * Model untuk nilai mata kuliah mahasiswa.
 * Menghitung IPS (Indeks Prestasi Semester) dan IPK (Indeks Prestasi Kumulatif),
 * serta menyediakan data transkrip nilai.
 */
class Nilai
{
    private PDO $db;

    /** Konversi nilai huruf -> bobot angka */
    public const BOBOT = [
        'A'  => 4.00,
        'A-' => 3.75,
        'B+' => 3.50,
        'B'  => 3.00,
        'B-' => 2.75,
        'C+' => 2.50,
        'C'  => 2.00,
        'D'  => 1.00,
        'E'  => 0.00,
    ];

    public function __construct()
    {
        $this->db = Database::getInstance()->getConnection();
    }

    /* ================= Mata Kuliah ================= */

    public function getAllMataKuliah(): array
    {
        return $this->db->query("SELECT * FROM mata_kuliah ORDER BY kode ASC")->fetchAll();
    }

    public function tambahMataKuliah(string $kode, string $nama, int $sks): bool
    {
        $stmt = $this->db->prepare("INSERT INTO mata_kuliah (kode, nama, sks) VALUES (?, ?, ?)");
        return $stmt->execute([$kode, $nama, $sks]);
    }

    /* ================= Nilai ================= */

    public function tambahNilai(int $mahasiswaId, int $mataKuliahId, int $semester, string $nilaiHuruf): bool
    {
        if (!array_key_exists($nilaiHuruf, self::BOBOT)) {
            return false;
        }
        $stmt = $this->db->prepare(
            "INSERT INTO nilai (mahasiswa_id, mata_kuliah_id, semester, nilai_huruf, bobot)
             VALUES (?, ?, ?, ?, ?)
             ON DUPLICATE KEY UPDATE semester = VALUES(semester),
                                     nilai_huruf = VALUES(nilai_huruf),
                                     bobot = VALUES(bobot)"
        );
        $ok = $stmt->execute([
            $mahasiswaId, $mataKuliahId, $semester, $nilaiHuruf, self::BOBOT[$nilaiHuruf],
        ]);

        if ($ok) {
            $this->updateIPK($mahasiswaId); // sinkronkan kolom ipk di tabel mahasiswa
        }
        return $ok;
    }

    public function hapusNilai(int $nilaiId): bool
    {
        // Ambil dulu mahasiswa_id agar IPK bisa dihitung ulang setelah hapus
        $stmt = $this->db->prepare("SELECT mahasiswa_id FROM nilai WHERE id = ?");
        $stmt->execute([$nilaiId]);
        $mahasiswaId = $stmt->fetchColumn();

        if (!$mahasiswaId) return false;

        $stmt = $this->db->prepare("DELETE FROM nilai WHERE id = ?");
        $ok = $stmt->execute([$nilaiId]);

        if ($ok) {
            $this->updateIPK((int) $mahasiswaId);
        }
        return $ok;
    }

    /** Semua nilai milik satu mahasiswa (join mata kuliah) */
    public function getNilaiByMahasiswa(int $mahasiswaId): array
    {
        $stmt = $this->db->prepare(
            "SELECT n.id, n.semester, n.nilai_huruf, n.bobot,
                    mk.kode, mk.nama AS mata_kuliah, mk.sks
             FROM nilai n
             JOIN mata_kuliah mk ON mk.id = n.mata_kuliah_id
             WHERE n.mahasiswa_id = ?
             ORDER BY n.semester ASC, mk.kode ASC"
        );
        $stmt->execute([$mahasiswaId]);
        return $stmt->fetchAll();
    }

    /* ================= Perhitungan IP ================= */

    /**
     * IPS = Σ(bobot × sks) / Σ(sks) untuk satu semester
     */
    public function hitungIPS(int $mahasiswaId, int $semester): float
    {
        $stmt = $this->db->prepare(
            "SELECT COALESCE(SUM(n.bobot * mk.sks) / NULLIF(SUM(mk.sks), 0), 0)
             FROM nilai n
             JOIN mata_kuliah mk ON mk.id = n.mata_kuliah_id
             WHERE n.mahasiswa_id = ? AND n.semester = ?"
        );
        $stmt->execute([$mahasiswaId, $semester]);
        return round((float) $stmt->fetchColumn(), 2);
    }

    /**
     * IPK = Σ(bobot × sks) / Σ(sks) untuk seluruh semester
     */
    public function hitungIPK(int $mahasiswaId): float
    {
        $stmt = $this->db->prepare(
            "SELECT COALESCE(SUM(n.bobot * mk.sks) / NULLIF(SUM(mk.sks), 0), 0)
             FROM nilai n
             JOIN mata_kuliah mk ON mk.id = n.mata_kuliah_id
             WHERE n.mahasiswa_id = ?"
        );
        $stmt->execute([$mahasiswaId]);
        return round((float) $stmt->fetchColumn(), 2);
    }

    /** Simpan hasil hitung IPK ke kolom mahasiswa.ipk agar dashboard tetap konsisten */
    public function updateIPK(int $mahasiswaId): void
    {
        $ipk  = $this->hitungIPK($mahasiswaId);
        $stmt = $this->db->prepare("UPDATE mahasiswa SET ipk = ? WHERE id = ?");
        $stmt->execute([$ipk, $mahasiswaId]);
    }

    /* ================= Transkrip ================= */

    /**
     * Transkrip nilai: dikelompokkan per semester,
     * setiap semester berisi daftar mata kuliah + IPS,
     * ditutup dengan total SKS dan IPK.
     */
    public function getTranskrip(int $mahasiswaId): array
    {
        $rows = $this->getNilaiByMahasiswa($mahasiswaId);

        $semesters = [];
        foreach ($rows as $row) {
            $semesters[$row['semester']]['matkul'][] = $row;
        }

        $totalSks = 0;
        foreach ($semesters as $smt => &$data) {
            $sksSemester = array_sum(array_column($data['matkul'], 'sks'));
            $data['total_sks'] = $sksSemester;
            $data['ips']       = $this->hitungIPS($mahasiswaId, (int) $smt);
            $totalSks         += $sksSemester;
        }
        unset($data);
        ksort($semesters);

        return [
            'semesters' => $semesters,
            'total_sks' => $totalSks,
            'ipk'       => $this->hitungIPK($mahasiswaId),
        ];
    }
}
