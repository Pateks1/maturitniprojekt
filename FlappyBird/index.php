<?php

// Spustí se sekce.
session_start();

include "functions.php"; 

// Podmínky pro přihlášení, pokud se uživatel jmenuje admin a heslo je admin tak to přihlásí do session. Pokud je špatně přihlášení tak to nepřihlásí a napíše to.
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if(isset($_POST['username']) && isset($_POST["password"])){
        $username = trim($_POST["username"]);
        $password = trim($_POST["password"]);
    
        if ($username === "admin" && $password === "admin") {
            $_SESSION["admin"] = true;
            echo "Byl jsi přihlášen.";
        } else {
            echo("Špatné jméno nebo heslo!");
        }
    }
}

// Když uživatel klikne na tlačítko odhlásit tak to odhlásí uživatele, smaže session a vrátí na hlavní stránku.
if (isset($_GET["logout"])) {
    session_destroy();
    header("Location: index.php");
    exit();
}

    // Dotaz na získání dat
    $sql = "SELECT u.user_id, u.username, MAX(s.score) AS score
            FROM 1AUsers u
            INNER JOIN 1AScores s ON u.user_id = s.user_id
            GROUP BY u.user_id, u.username
            ORDER BY score DESC;";
    $result = $conn->query($sql);

?>



<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="author" content="Štěpán Pátek">

    <!-- Přidání předpřipravené knihovny pro css Bootstrap, a scripty taky -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <script src="https://code.jquery.com/jquery-3.2.1.slim.min.js" integrity="sha384-KJ3o2DKtIkvYIK3UENzmM7KCkRr/rE9/Qpg6aAZGJwFDMVNA/GpGFF93hXpG5KkN" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/popper.js@1.12.9/dist/umd/popper.min.js" integrity="sha384-ApNbgh9B+Y1QKtv3Rn7W3mgPxhU9K/ScQsAP7hUibX39j7fakFPskvXusvfa0b4Q" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.0.0/dist/js/bootstrap.min.js" integrity="sha384-JZR6Spejh4U02d8jOt6vLEHfe/JQGiRRSQQxSfFWpi1MquVdAyjUar5+76PVCmYl" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.min.js" integrity="sha384-0pUGZvbkm6XF6gxjEnlmuGrJXVbNuzT9qBBavbLwCsOGabYfZo0T0to5eqruptLy" crossorigin="anonymous"></script>
    <link rel="stylesheet" href="style.css">
    <title>Maturitní projekt</title>
</head>
<body>

    <!-- Vyskakující okno pro formulář pro přihlášení.-->
    <div class="modal fade" id="loginModal" tabindex="-1" role="dialog" aria-labelledby="loginModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header bg-dark">
            <h5 class="modal-title" id="loginModalLabel">Přihlásit se</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body bg-dark text-center">
            <form action="index.php" method="post">
                <div class="mb-3">
                <label for="username" class="form-label">Uživatelské jméno</label>
                <input type="text" class="form-control" id="username" name="username"/>
                </div>
                <div class="mb-3">
                <label for="password" class="form-label">Heslo</label>
                <input type="password" class="form-control" id="password" name="password"/>
                </div>
                <button type="submit" class="btn btn-primary">Přihlásit</button>
            </form>
            </div>
        </div>
        </div>
    </div>


    <!-- Přihlašovací tlačítko, obrázek školy a název stránky.-->
    <nav class="navbar navbar-expand-sm">
      <div class="container">
        <a href="https://new.spskladno.cz"><img src="logo.png" alt="Logo" height="80" /></a>
        <h1 class="title">Maturitní projekt Pátek</h1>
        <div class="nav-navbar">
            <a data-bs-toggle="modal" data-bs-target="#loginModal" class="acko">Přihlásit se</a>
        </div>
      </div>
    </nav>
    <main>


    <!-- Jestli se uživatel přihlásí tak se zobrazí tlačítko pro odhlášení.-->
    <?php if (!isset($_SESSION["admin"])): ?>
    <?php else: ?>
        <a href="index.php?logout=true" class="btn btn-danger logout-btn">Odhlásit se</a>
    <?php endif; ?>

    <!-- Tabulka se záznamy-->
    <div class="container w-50 p-4">
        <table class="table table-bordered table-striped">
            <thead>
                <tr class="table-primary">
                <th scope="col">Jméno/přezdívka</th>
                <th scope="col">Skóre</th>
                <!-- Sloupec se zobrazí zda-li je uživatel přihlášený jako admin.-->
                <?php if (isset($_SESSION["admin"])): ?> <th>Smazání záznamu</th> <?php endif; ?>
                </tr>
            </thead>
            <?php
            // Bere data z databáze a bude to probíhat dokud budou záznamy v databázi.
            if ($result->num_rows > 0) {
                while ($row = $result->fetch_assoc()) {
                    echo "<tr>
                            <td>" . htmlspecialchars($row["username"]) . "</td>
                            <td>" . htmlspecialchars($row["score"]) . "</td>";
                    // Zobrazí se zda-li je uživatel přihlášený.
                    if (isset($_SESSION["admin"])) {
                        echo "<td><a href='delete.php?id=" . $row["user_id"] . "' onclick='return confirm(\"Opravdu chcete smazat tento záznam?\")' class='text-danger becko'>❌</a></td>";
                    }
                    echo "</tr>";
                }
                // Zda-li nejsou data v databázi tak to vypíše níže. 
            } else {
                echo "<tr><td colspan='3'>Žádná data</td></tr>";
            }
            ?>
            </tbody>
        </table>
    </div>

    <hr>

    <section class="container w-auto p-3">
        <h2 class="p-3 font-weight-bold">Popis programu</h2>
        <p class="pecko">
        <h3>Kroky programu:</h3>
 Tento program je klon hry Flappy Bird vytvořený v Pythonu s využitím Pygame. Hráč ovládá ptáčka, který musí prolétávat mezi trubkami – skáče stiskem mezerníku a hra končí při kolizi. Skóre se ukládá do souboru i databáze. Trubky se generují s náhodnou výškou, skóre se aktualizuje při úspěšném průletu a nejvyšší skóre se uchovává. Herní logika zahrnuje pohyb, kolize, animace a menu pro zadání jména hráče. Každý hráč (1AUsers) může mít více skóre záznamů (1AScores), což odpovídá vztahu 1:N
    <p></p>
    <p><strong>1. Inicializace:</strong></p>
    <p>- Načtení knihoven</p>
    <p>- Nastavení herního okna, obrázků a proměnných</p>

    <p><strong>2. Připojení k databázi MariaDB a vytvoření tabulek:</strong></p>
    <p>- `1AUsers` (uživatelé) s primárním klíčem `user_id`</p>
    <p>- `1AScores` (skóre), kde `user_id` je cizí klíč (vztah 1:N)</p>

    <p><strong>3. Funkce hlavního menu:</strong></p>
    <p>- Požádá hráče o zadání jména</p>
    <p>- Pokud uživatel existuje, načte jeho ID a nejvyšší skóre</p>
    <p>- Jinak přidá nového uživatele do databáze</p>

    <p><strong>4. Herní smyčka:</strong></p>
    <p>- Pokud hráč stiskne MEZERNÍK: nastaví směr ptáka nahoru</p>
    <p>- Jinak postupně zvýší pádovou rychlost ptáka vlivem gravitace a posune ho směrem dolů.</p>
    <p>- Posun překážek doleva</p>
    <p>- Pokud pták narazí na překážku nebo podlahu:</p>
    <p>  - Nastaví stav **Game Over**</p>
    <p>  - Zkontroluje, zda je skóre vyšší než uložené skóre v databázi</p>
    <p>  - Pokud ano, **uloží nové skóre do databáze**</p>

    <p><strong>5. Funkce pro zápis skóre do databáze:</strong></p>
    <p>- Pokud hráč porazil své nejvyšší skóre:</p>
    <p>  - Aktualizuje hodnotu ve `1AScores` pro daného `user_id`</p>
    <p>  - Pokud uživatel neměl žádné skóre, vloží nový záznam</p>

    <p><strong>6. Konec hry:</strong></p>
    <p>- Zobrazí výsledné skóre a umožní restart hry nebo ukončení aplikace</p>
    </section>
    <hr>
    
    <section class="container w-auto p-4">
        <h2 class="p-3 font-weight-bold">Požitý algoritmy a knihovny</h2>
        <p class="pecko">
    <p>pygame – Knihovna pro tvorbu 2D her.</p>
    <p>sys – Umožňuje práci se systémovými funkcemi, například ukončení programu.</p>
    <p>time – Poskytuje funkce pro práci s časem.</p>
    <p>random – Generování náhodných hodnot (např. výška trubek).</p>
    <p>os – Práce se soubory a operačním systémem.</p>
    <p>mariadb – Připojení k databázi MariaDB.</p>        
        </p>
        <img src="image.png" class="mx-auto d-block img-responsive" alt="Vývojový diagram">
    </section>
    <hr>
    <section class="container w-auto p-4">
        <h2 class="p-3 font-weight-bold">Seznam autorů</h2>
        <p class="pecko">
            Štěpán Pátek
        </p>
    </section>
    <hr>
    <section class="container w-auto p-4">
        <h2 class="p-3 font-weight-bold">Odkaz pro git</h2>
        <p class="pecko">
            Všechno je uloženo na tomto Gitu: <a href="https://github.com/Pateks1/maturitniprojekt">zde</a>
        </p>
    </section>
    
    <!-- Ukončení spojeni s databází-->
    <?php $conn -> close();?>
    </main>
    <!-- Footer, kde se bude měnit rok automaticky.-->
    <footer>
        <div class="text-center p-3" style="background-color: rgba(0,0,0,0.05)">&copy; <?php echo date("Y"); ?> Štěpán Pátek, všechna práva vyhrazena.</div>
    </footer>
</body>
</html>
