<?php
session_start();

/**
 * Mengambil daftar tugas dari session.
 * Data contoh hanya diisi saat session belum punya daftar.
 *
 * @return array<int, array{id: int, nama: string, selesai: bool}> Daftar tugas.
 */
function muatTugas(): array
{
    if (!isset($_SESSION['tugas'])) {
        $_SESSION['tugas'] = [
            ['id' => 1, 'nama' => 'Belajar PHP', 'selesai' => false],
            ['id' => 2, 'nama' => 'Kerjakan tugas UX', 'selesai' => true],
        ];
    }

    return $_SESSION['tugas'];
}

/**
 * Mengubah status selesai sebuah tugas.
 *
 * @param int $id ID tugas yang ditandai.
 * @param bool $selesai True jika selesai, false jika belum.
 * @return void
 */
function tandaiSelesai(int $id, bool $selesai): void
{
    if ($id < 1) {
        return;
    }

    foreach (muatTugas() as $index => $item) {
        if ($item['id'] === $id) {
            $_SESSION['tugas'][$index]['selesai'] = $selesai;
            return;
        }
    }
}

/**
 * Menghapus satu tugas dari daftar.
 *
 * @param int $id ID tugas yang dihapus.
 * @return void
 */
function hapusTugas(int $id): void
{
    if ($id < 1) {
        return;
    }

    // array_values merapikan indeks agar nomor di tabel tetap berurutan.
    $_SESSION['tugas'] = array_values(array_filter(
        muatTugas(),
        fn (array $item): bool => $item['id'] !== $id
    ));
}

/**
 * Mengubah nama sebuah tugas.
 *
 * @param int $id ID tugas yang diubah.
 * @param string $nama Nama tugas yang baru.
 * @return void
 */
function ubahTugas(int $id, string $nama): void
{
    $nama = trim($nama);
    if ($id < 1 || $nama === '') {
        return;
    }

    foreach (muatTugas() as $index => $item) {
        if ($item['id'] === $id) {
            $_SESSION['tugas'][$index]['nama'] = $nama;
            return;
        }
    }
}

/**
 * Menambahkan tugas baru ke daftar.
 *
 * @param string $nama Nama tugas.
 * @return void
 */
function tambahTugas(string $nama): void
{
    $nama = trim($nama);
    if ($nama === '') {
        return;
    }

    $tugas = muatTugas();
    $idTerakhir = 0;
    foreach ($tugas as $item) {
        $idTerakhir = max($idTerakhir, $item['id']);
    }

    $_SESSION['tugas'][] = [
        'id' => $idTerakhir + 1,
        'nama' => $nama,
        'selesai' => false,
    ];
}

/**
 * Menampilkan tombol dan modal tambah tugas.
 *
 * @return void
 */
function tampilkanForm(): void
{
    ?>
    <div class="mb-4">
        <button class="btn btn-primary" type="button" data-bs-toggle="modal" data-bs-target="#modalTambah">Tambah tugas</button>
    </div>
    <div class="modal fade" id="modalTambah" tabindex="-1" aria-labelledby="modalTambahLabel" aria-hidden="true">
        <div class="modal-dialog">
            <form class="modal-content" method="post">
                <div class="modal-header">
                    <h2 class="modal-title fs-5" id="modalTambahLabel">Tambah tugas</h2>
                    <button class="btn-close" type="button" data-bs-dismiss="modal" aria-label="Tutup"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="aksi" value="tambah">
                    <label class="form-label" for="nama">Nama tugas</label>
                    <input class="form-control" type="text" id="nama" name="nama" required>
                </div>
                <div class="modal-footer">
                    <button class="btn btn-secondary" type="button" data-bs-dismiss="modal">Batal</button>
                    <button class="btn btn-primary" type="submit">Simpan</button>
                </div>
            </form>
        </div>
    </div>
    <?php
}

/**
 * Menampilkan tabel daftar tugas.
 *
 * @param array<int, array{id: int, nama: string, selesai: bool}> $tasks Daftar tugas yang ditampilkan.
 * @return void
 */
function tampilkanDaftar(array $tasks): void
{
    ?>
    <table class="table table-striped">
        <thead>
            <tr>
                <th scope="col">No</th>
                <th scope="col">Selesai</th>
                <th scope="col">Tugas</th>
                <th scope="col">Status</th>
                <th scope="col">Aksi</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($tasks as $index => $item): ?>
                <?php
                // Ubah nama jadi teks biasa supaya isian pengguna tidak ikut terbaca sebagai HTML.
                $nama = htmlspecialchars($item['nama'], ENT_QUOTES, 'UTF-8');
                ?>
                <tr>
                    <td><?= $index + 1 ?></td>
                    <td>
                        <form method="post">
                            <input type="hidden" name="aksi" value="selesai">
                            <input type="hidden" name="id" value="<?= (int) $item['id'] ?>">
                            <input
                                class="form-check-input"
                                type="checkbox"
                                name="selesai"
                                value="1"
                                aria-label="Tandai <?= $nama ?> selesai"
                                onchange="this.form.submit()"
                                <?= $item['selesai'] ? 'checked' : '' ?>
                            >
                        </form>
                    </td>
                    <td class="<?= $item['selesai'] ? 'text-decoration-line-through' : '' ?>"><?= $nama ?></td>
                    <td><?= $item['selesai'] ? 'Selesai' : 'Belum selesai' ?></td>
                    <td>
                        <div class="d-flex gap-2">
                            <button class="btn btn-sm btn-outline-secondary" type="button" data-bs-toggle="modal" data-bs-target="#modalEdit<?= (int) $item['id'] ?>">Edit</button>
                            <form method="post">
                                <input type="hidden" name="aksi" value="hapus">
                                <input type="hidden" name="id" value="<?= (int) $item['id'] ?>">
                                <button class="btn btn-sm btn-danger" type="submit">Hapus</button>
                            </form>
                        </div>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
    <?php foreach ($tasks as $item): ?>
        <?php $nama = htmlspecialchars($item['nama'], ENT_QUOTES, 'UTF-8'); ?>
        <div class="modal fade" id="modalEdit<?= (int) $item['id'] ?>" tabindex="-1" aria-labelledby="modalEditLabel<?= (int) $item['id'] ?>" aria-hidden="true">
            <div class="modal-dialog">
                <form class="modal-content" method="post">
                    <div class="modal-header">
                        <h2 class="modal-title fs-5" id="modalEditLabel<?= (int) $item['id'] ?>">Edit tugas</h2>
                        <button class="btn-close" type="button" data-bs-dismiss="modal" aria-label="Tutup"></button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" name="aksi" value="ubah">
                        <input type="hidden" name="id" value="<?= (int) $item['id'] ?>">
                        <label class="form-label" for="namaEdit<?= (int) $item['id'] ?>">Nama tugas</label>
                        <input class="form-control" type="text" id="namaEdit<?= (int) $item['id'] ?>" name="nama" value="<?= $nama ?>" required>
                    </div>
                    <div class="modal-footer">
                        <button class="btn btn-secondary" type="button" data-bs-dismiss="modal">Batal</button>
                        <button class="btn btn-primary" type="submit">Simpan</button>
                    </div>
                </form>
            </div>
        </div>
    <?php endforeach; ?>
    <?php
}

// Selain selesai, ubah, dan hapus dianggap tambah.
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $aksi = $_POST['aksi'] ?? 'tambah';
    $id = (int) ($_POST['id'] ?? 0);

    if ($aksi === 'selesai') {
        // Checkbox yang tidak dicentang tidak ikut terkirim.
        tandaiSelesai($id, isset($_POST['selesai']));
    } elseif ($aksi === 'ubah') {
        ubahTugas($id, $_POST['nama'] ?? '');
    } elseif ($aksi === 'hapus') {
        hapusTugas($id);
    } else {
        tambahTugas($_POST['nama'] ?? '');
    }

    // Redirect agar refresh tidak mengirim form yang sama lagi.
    header('Location: index.php');
    exit;
}

$tasks = muatTugas();
?>
<!DOCTYPE html>
<html lang="id" data-bs-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>To-Do List</title>
    <link rel="stylesheet" href="assets/bootstrap-5.3.8/css/bootstrap.min.css">
</head>
<body>
    <main class="container py-5">
        <h1 class="mb-4">Daftar Tugas</h1>
        <?php tampilkanForm(); ?>
        <?php tampilkanDaftar($tasks); ?>
    </main>
    <script src="assets/bootstrap-5.3.8/js/bootstrap.bundle.min.js"></script>
</body>
</html>
