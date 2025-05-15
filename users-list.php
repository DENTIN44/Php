<?php
// users-list.php

require_once 'Models/Database.php';
require_once 'Controllers/UserController.php';

try {
    $database = new Database(__DIR__ . '/');
    $conn     = $database->getConnection();
    $database->createTables();      // ensure tables exist

    $userController = new UserHandler($conn);
    // initial load: no search filter
    $users = $userController->getAllUsers();
} catch (Exception $e) {
    exit('Error: ' . $e->getMessage());
}
?><!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>User List</title>
  <link rel="stylesheet" href="Assets/user-list.css">
</head>
<body>
  <div class="form-group">
    <a href="index.php">Back to Home</a>
  </div>

  <table>
    <thead>
      <tr>
        <th>ID</th>
        <th>Name</th>
        <th>Email</th>
        <th>Created At</th>
        <th>Updated At</th>
        <th>Actions</th>
      </tr>
    </thead>
    <tbody>
      <?php foreach ($users as $u): ?>
      <tr>
        <td><?= htmlspecialchars($u['id']) ?></td>
        <td><?= htmlspecialchars($u['username']) ?></td>
        <td><?= htmlspecialchars($u['email']) ?></td>
        <td><?= htmlspecialchars($u['createdAt']) ?></td>
        <td><?= htmlspecialchars($u['updatedAt']) ?></td>
        <td>
          <form method="POST" action="delete.php" style="display:inline">
            <input type="hidden" name="user_id" value="<?= $u['id'] ?>">
            <button onclick="return confirm('Delete this user?')">Delete</button>
          </form>
          <form method="GET" action="user-update.php" style="display:inline">
            <input type="hidden" name="user_id" value="<?= $u['id'] ?>">
            <button>Edit</button>
          </form>
        </td>
      </tr>
      <?php endforeach ?>
    </tbody>
  </table>
</body>
</html>
