<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <!-- Agregar Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
        }
        .container {
            background: #fff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            width: 400px;
            text-align: center;
        }
        h2 {
            margin-bottom: 20px;
            color: #333;
        }
        label {
            display: block;
            margin-bottom: 5px;
            color: #666;
        }
        input[type="text"],
        input[type="password"] {
            width: 85%;
            padding: 10px;
            margin-bottom: 20px;
            border: 1px solid #ddd;
            border-radius: 4px;
        }
        .password-container {
            display: flex;
            align-items: center;
            width: 90%;
            margin: 0 auto;
        }
        .password-container input {
            flex: 1;
        }
        .password-container button {
            background: none;
            border: none;
            cursor: pointer;
            padding: 0 5px;
            color: #666;
            font-size: 18px;
        }
        button[type="submit"] {
            background: #007bff;
            color: #fff;
            border: none;
            padding: 10px 15px;
            border-radius: 4px;
            cursor: pointer;
            font-size: 16px;
        }
        button[type="submit"]:hover {
            background: #0056b3;
        }
    </style>
</head>
<body>
    <div class="container">
        <h2>Login</h2>
        <form method="post" action="login_process.php">
            <img src="imagenes/logo .jpg" alt="Logo" />
            <br>
            <label for="username">Usuario:</label>
            <input type="text" id="username" name="username" required><br>
            <br>
            <label for="password">Contrase&ntilde;a:</label>
            <div class="password-container">
                <input type="password" id="password" name="password" required>
                <button type="button" onclick="togglePassword()">
                    <i class="fas fa-eye"></i>
                </button>
            </div>
            <br>
            <button type="submit">Iniciar sesi&oacute;n</button>
        </form>
    </div>

    <script>
        function togglePassword() {
            const passwordInput = document.getElementById("password");
            const icon = passwordInput.nextElementSibling.querySelector("i");
            if (passwordInput.type === "password") {
                passwordInput.type = "text";
                icon.classList.remove("fa-eye");
                icon.classList.add("fa-eye-slash");
            } else {
                passwordInput.type = "password";
                icon.classList.remove("fa-eye-slash");
                icon.classList.add("fa-eye");
            }
        }
    </script>
</body>
</html>
