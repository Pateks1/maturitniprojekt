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
    $sql = "SELECT 1AUsers.user_id, username, score FROM 1AUsers INNER JOIN 1AScores ON 1AUsers.user_id = 1AScores.user_id ORDER BY score DESC";
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
            Lorem ipsum dolor sit amet consectetur adipisicing elit. Molestiae voluptatibus nesciunt illum fugiat hic quis dignissimos doloribus, non quaerat vel est, animi eius dolorum, optio nam itaque unde fuga rerum cupiditate similique ipsa dolor. Nesciunt a at vitae quasi illo deleniti aliquam et, reiciendis soluta fugiat, ex ducimus error mollitia consequuntur hic rerum nostrum, exercitationem qui beatae non molestias! Dolorum delectus nobis ea iure at autem, repellendus expedita eos ipsum minus. Voluptate, doloribus? Aliquid recusandae aspernatur ab, consectetur nam doloremque totam eaque placeat, fuga assumenda officia, obcaecati nobis natus eligendi. Vitae voluptatum beatae cum voluptates eaque est tempore pariatur culpa, autem odio accusantium at similique quod voluptas doloribus placeat dolore quis minima quibusdam debitis blanditiis eius voluptate corporis aliquid. Sit aut dicta eveniet veritatis ipsa nulla maiores. Tempore nobis itaque rerum, asperiores doloribus maxime in, accusantium ab odio dolorem saepe eius modi enim reprehenderit? Nesciunt harum quis corrupti consequuntur, minima enim alias officia sint quibusdam id deleniti numquam sed magni voluptas cumque, repellendus laboriosam esse in nobis libero quaerat inventore dicta excepturi nostrum! Molestias placeat recusandae in quis neque similique dicta sint magni eaque error sapiente, ratione facere porro laborum deserunt dolores minus voluptatibus, sit odit modi ad voluptates maiores?
        </p>
    </section>
    <hr>
    <section class="container w-auto p-4">
        <h2 class="p-3 font-weight-bold">Požitý algoritmy a knihovny</h2>
        <p class="pecko">
            Lorem ipsum dolor sit amet consectetur adipisicing elit. Dolor consectetur labore harum autem nam facilis laboriosam et, aspernatur corporis rerum obcaecati qui assumenda in ipsam veritatis ducimus quaerat dolorum natus libero iusto atque nemo molestias! Animi quas delectus perferendis tempore mollitia assumenda modi laboriosam veniam. Eligendi sed maxime soluta dolore asperiores, aperiam delectus, repellat est ea commodi accusantium laudantium suscipit, consequuntur deserunt odit explicabo in molestiae odio harum eius. Eveniet, exercitationem est accusamus a et quaerat obcaecati voluptatum earum, nam deleniti, consequatur error esse praesentium perferendis odio temporibus soluta voluptas officia dolor inventore facilis! Dolores assumenda harum saepe voluptate eligendi porro ut illum tempora adipisci. A expedita quidem officia quisquam, nobis dolorem cum, atque sequi officiis illum eum, tenetur vel! Itaque maxime dicta rerum velit praesentium explicabo similique provident iusto.
        </p>
        <img src="image.png" class="mx-auto d-block img-responsive" alt="Vývojový diagram">
    </section>
    <hr>
    <section class="container w-auto p-4">
        <h2 class="p-3 font-weight-bold">Seznam autorů</h2>
        <p class="pecko">
            Lorem ipsum dolor sit amet consectetur adipisicing elit. Enim, dolor, maiores perferendis iusto repellat incidunt porro consequatur beatae itaque pariatur unde nemo architecto aliquam ad. Ipsa soluta necessitatibus reiciendis atque.
        </p>
    </section>
    <hr>
    <section class="container w-auto p-4">
        <h2 class="p-3 font-weight-bold">Odkaz pro git</h2>
        <p class="pecko">
            Všechno je uloženo na tomto Gitu: <a href="https://spsrakovnik.tech/macek.mi.2021/NaDoma/Snake/">zde</a>
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