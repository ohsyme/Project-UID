<?php
include('../database/db_connect.php'); // Include your PDO connection
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['user_id'])) {
    header("Location: ../login_user.php");
    exit;
}

$user_id = $_SESSION['user_id'];

try {
    $stmt = $pdo->prepare("
    SELECT users.*, seller_information.*
    FROM users
    LEFT JOIN seller_information ON users.user_id = seller_information.user_id
    WHERE users.user_id = :id
    ");
    $stmt->bindParam(':id', $user_id, PDO::PARAM_INT);  
    $stmt->execute();
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$user) {
        header("Location: ../login_user.php");
        exit;
    }
} catch (PDOException $e) {
    echo "Query failed: " . $e->getMessage();
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta content="width=device-width, initial-scale=1.0" name="viewport" />
    <title>Tambah Layanan</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css" rel="stylesheet" />
</head>

<body class="bg-gray-100 font-sans">
    <div class="flex flex-col min-h-screen">
        <!-- Header -->
        <header class="bg-gray-800 shadow-md p-4 flex justify-between items-center">
            <h1 class="text-2xl font-bold text-white">
                <a href="./../index.php">SiBantu</a>
            </h1>
            <div class="flex items-center gap-4">
                <i class="fas fa-bell text-white text-lg"></i>
                <i class="fas fa-envelope text-white text-lg"></i>
                <img src="<?= $user['profile_picture'] ? 'data:image/jpeg;base64,' . $user['profile_picture'] : 'https://storage.googleapis.com/a1aa/image/cCYjTRgvAFZBA5oP1xaxRnauVzPZZiKo62ESgUGl9aVxeG7JA.jpg' ?>"
                    class="rounded-full w-10 h-10 border-2 border-white" />
            </div>
        </header>

        <div class="flex flex-1 flex-col md:flex-row">
            <!-- Sidebar -->
            <aside class="w-full md:w-64 bg-white p-6 shadow-md">
                <div class="flex items-center gap-3 mb-6">
                    <img src="<?= $user['profile_picture'] ? 'data:image/jpeg;base64,' . $user['profile_picture'] : 'https://storage.googleapis.com/a1aa/image/cCYjTRgvAFZBA5oP1xaxRnauVzPZZiKo62ESgUGl9aVxeG7JA.jpg' ?>"
                        class="rounded-full w-12 h-12">
                    <div>
                        <h2 class="text-lg font-semibold"><?= htmlspecialchars($user['name'] ?? 'User') ?></h2>
                        <p class="text-gray-500 text-sm"><?= htmlspecialchars($user['usertype'] ?? 'User') ?></p>
                    </div>
                </div>

                <!-- Dropdown -->
                <div>
                    <button onclick="toggleDropdown('transaksiDropdown')"
                        class="w-full bg-gray-100 px-4 py-2 rounded flex justify-between items-center text-gray-700 font-medium hover:bg-gray-200">
                        Transaksi
                        <i class="fas fa-chevron-down ml-2"></i>
                    </button>
                    <div id="transaksiDropdown" class="hidden mt-2">
                        <a href="provider_add-../database/seller-service.php"
                            class="block px-4 py-2 hover:bg-gray-100 rounded">Service</a>
                        <a href="provider_transaction-history.php"
                            class="block px-4 py-2 hover:bg-gray-100 rounded">Riwayat Transaksi</a>
                    </div>
                </div>
            </aside>

            <!-- Main Content -->
            <main class="flex-1 p-6">
                <div class="bg-white p-8 rounded-xl shadow-md">
                    <!-- Tabs -->
                    <div class="flex gap-6 border-b pb-4 mb-6">
                        <a href="provider-dashboard.php" class="text-gray-500 hover:text-blue-600">Profil Penyedia
                            Jasa</a>
                        <a href="provider_user-reviews.php" class="text-gray-500 hover:text-blue-600">User Review</a>
                    </div>

                    <h2 class="text-2xl font-bold mb-6">Tambah Layanan</h2>

                    <form method="POST" id="serviceForm">
                        <div class="mb-4">
                            <label for="service_name" class="block text-gray-700">Nama Layanan</label>
                            <input type="text" name="service_name" id="service_name"
                                class="w-full p-3 border rounded-md" required>
                        </div>

                        <div class="mb-4">
                            <label for="service_description" class="block text-gray-700">Deskripsi Layanan</label>
                            <textarea name="service_description" id="service_description"
                                class="w-full p-3 border rounded-md" required></textarea>
                        </div>

                        <div class="mb-4">
                            <label for="service_price" class="block text-gray-700">Harga Layanan</label>
                            <input type="number" name="service_price" id="service_price"
                                class="w-full p-3 border rounded-md" required>
                        </div>

                        <div class="text-center">
                            <button type="button" id="submitService"
                                class="bg-gray-800 text-white px-6 py-3 rounded-full shadow hover:bg-gray-700 transition">
                                Tambah Layanan
                            </button>
                        </div>
                    </form>

                    <div id="serviceList" class="space-y-4 mt-6">
                        <!-- Services will be loaded here by JS -->
                    </div>
                </div>
            </main>
        </div>

        <!-- Footer -->
        <footer class="bg-gray-800 text-white py-10 mt-auto">
            <div class="max-w-screen-xl mx-auto px-6 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-8">
                <div>
                    <h2 class="text-xl font-bold mb-2">SiBantu</h2>
                    <p class="text-sm">Mitra andalan Anda untuk layanan sehari-hari. Hubungi kami kapan saja, di mana
                        saja.</p>
                </div>
                <div>
                    <h3 class="font-semibold text-lg mb-2">Quick Links</h3>
                    <ul class="space-y-1 text-sm">
                        <li><a href="index.php" class="hover:underline">Home</a></li>
                        <li><a href="faq.php" class="hover:underline">FAQ</a></li>
                    </ul>
                </div>
                <div>
                    <h3 class="font-semibold text-lg mb-2">Contact Us</h3>
                    <ul class="space-y-1 text-sm">
                        <li><a href="mailto:support@sibantu.com" class="hover:underline">support@sibantu.com</a></li>
                        <li><a href="tel:+6281234567890" class="hover:underline">+62 812 3456 7890</a></li>
                    </ul>
                </div>
            </div>
        </footer>
    </div>

    <script>
        // Function to load services
        function loadServices() {
            fetch('../database/seller-service.php', {
                    method: 'POST',
                    body: new URLSearchParams({
                        action: 'getservice',
                    }),
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded'
                    }
                })
                .then(response => response.json())
                .then(data => {
                    const serviceList = document.getElementById('serviceList');
                    serviceList.innerHTML = ''; // Clear existing content

                    if (data.success && data.services) {
                        data.services.forEach(service => {
                            // Determine badge class based on status
                            let badgeClass = '';
                            if (service.status === 'approved') {
                                badgeClass = 'bg-green-100 text-green-800';
                            } else if (service.status === 'pending') {
                                badgeClass = 'bg-yellow-100 text-yellow-800';
                            } else if (service.status === 'rejected') {
                                badgeClass = 'bg-red-100 text-red-800'; 
                            }

                            const serviceDiv = document.createElement('div');
                            serviceDiv.classList.add('bg-gray-50', 'p-4', 'rounded-lg', 'border', 'border-gray-200');
                            serviceDiv.innerHTML = `
                                <div class="flex justify-between items-start">
                                    <div class="flex-1">
                                        <h4 class="font-semibold text-lg">${service.service_name}</h4>
                                        <p class="text-gray-600 mt-1">${service.service_description}</p>
                                        <p class="text-gray-800 font-medium mt-2">Rp ${parseInt(service.service_price).toLocaleString()}</p>
                                    </div>
                                    <div class="ml-4 flex flex-row items-center space-x-2">
                                        <span class="px-4 py-1 rounded-full text-sm font-medium ${badgeClass}">
                                            Status: ${service.status.charAt(0).toUpperCase() + service.status.slice(1)}
                                        </span>
                                        <button onclick="deleteService(${service.service_id})" class="bg-red-500 text-white px-4 py-2 rounded-full hover:bg-red-600 transition">
                                            <i class="fas fa-trash-alt"></i> Hapus
                                        </button>
                                    </div>
                                </div>
                            `;

                            serviceList.appendChild(serviceDiv);
                        });
                    } else {
                        serviceList.innerHTML = `<p class="text-center text-gray-500">No services found.</p>`;
                    }
                })
            .catch(error => console.error('Error loading services:', error));
        }


        // Function to add service
        document.getElementById('submitService').addEventListener('click', function () {
            const serviceName = document.getElementById('service_name').value;
            const serviceDescription = document.getElementById('service_description').value;
            const servicePrice = document.getElementById('service_price').value;

            // Check if required fields are filled
            if (!serviceName || !servicePrice) {
                alert('Nama layanan dan harga wajib diisi');
                return;
            }

            fetch('../database/seller-service.php', {
                    method: 'POST',
                    body: new URLSearchParams({
                        action: 'add',
                        nama_layanan: serviceName,
                        deskripsi: serviceDescription,
                        harga: servicePrice
                    }),
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded'
                    }
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert('Service added successfully');
                        loadServices(); // Reload services
                    } else {
                        alert('Error deleting service');
                    }
                });
        });


        // Function to delete service
        function deleteService(serviceId) {
            if (confirm('Apakah Anda yakin ingin menghapus layanan ini?')) {
                fetch('../database/seller-service.php', {
                        method: 'POST',
                        body: new URLSearchParams({
                            action: 'delete',
                            service_id: serviceId
                        }),
                        headers: {
                            'Content-Type': 'application/x-www-form-urlencoded'
                        }
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            alert('Service deleted successfully');
                            loadServices(); // Reload services
                        } else {
                            alert('Error deleting service');
                        }
                    });
            }
        }

        loadServices();



        function toggleDropdown(id) {
            var dropdown = document.getElementById(id);
            if (dropdown.classList.contains('hidden')) {
                dropdown.classList.remove('hidden');
            } else {
                dropdown.classList.add('hidden');
            }
        }
        
    </script>
</body>

</html>