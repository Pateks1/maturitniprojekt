import pygame
import sys
import time
import random
import os
import mariadb

# Inicializace pygame
pygame.init()

# FPS
clock = pygame.time.Clock()

# Default values
userID = 0
highest_score = 0

# Funkce pro vykreslení základny
def draw_floor():
    screen.blit(floor_img, (floor_x, 490))
    screen.blit(floor_img, (floor_x + 290, 490)) 

# Vytvoří nové trubky s náhodnou výškou a vrátí jejich souřadnice.
def create_pipes():
    rozsah  = list(range(*pipe_height))
    pipe_y = random.choice(rozsah)
    top_pipe = pipe_img.get_rect(midbottom=(467, pipe_y - 150))
    bottom_pipe = pipe_img.get_rect(midtop=(467, pipe_y))
    return top_pipe, bottom_pipe

# Funkce pro animaci trubek
def pipe_animation():
    global game_over, score_time
    for pipe in pipes:
        if pipe.top < 0:
            flipped_pipe = pygame.transform.flip(pipe_img, False, True)
            screen.blit(flipped_pipe, pipe)
        else:
            screen.blit(pipe_img, pipe)

        pipe.centerx -= 3
        if pipe.right < 0:
            pipes.remove(pipe)

        if bird_rect.colliderect(pipe):
            game_over = True

# Funkce pro vykreslení skóre
def draw_score(game_state):
    if game_state == "game_on":
        score_text = score_font.render(f"{player_name}: {score}", True, (255, 255, 255))
        score_rect = score_text.get_rect(center=(width // 2, 66))
        screen.blit(score_text, score_rect)
    elif game_state == "game_over":
        score_text = score_font.render(f"{player_name}: Score: {score}", True, (255, 255, 255))
        score_rect = score_text.get_rect(center=(width // 2, 66))
        screen.blit(score_text, score_rect)

        high_score_text = score_font.render(f"High Score: {high_score}", True, (255, 255, 255))
        high_score_rect = high_score_text.get_rect(center=(width // 2, 470))
        screen.blit(high_score_text, high_score_rect)

# Funkce pro aktualizaci skóre
def score_update():
    global score, score_time, high_score, player_name
    if pipes:
        for pipe in pipes:
            if 65 < pipe.centerx < 69 and score_time:
                score += 1
                score_time = False
            if pipe.left <= 0:
                score_time = True

    if score > high_score:
        high_score = score
        save_high_score(player_name, score)

# Funkce pro potvrzení ukonření hry
def confirm_exit():
    confirm_screen = pygame.display.set_mode((288, 512))
    background = pygame.image.load("./sprites/pozadi_den.png")

    font = pygame.font.Font("freesansbold.ttf", 20)
    text = font.render("Chceš opravdu ukončit hru?", True, (255, 255, 255))
    text_rect = text.get_rect(center=(144, 200))

    yes_button = pygame.Rect(60, 300, 80, 40)
    no_button = pygame.Rect(160, 300, 80, 40)

    running = True
    while running:
        confirm_screen.blit(background, (0, 0))
        confirm_screen.blit(text, text_rect)

        pygame.draw.rect(confirm_screen, (0, 255, 0), yes_button)
        pygame.draw.rect(confirm_screen, (255, 0, 0), no_button)

        yes_text = font.render("Ano", True, (0, 0, 0))
        no_text = font.render("Ne", True, (0, 0, 0))
        confirm_screen.blit(yes_text, (80, 310))
        confirm_screen.blit(no_text, (190, 310))

        pygame.display.update()

        for event in pygame.event.get():
            if event.type == pygame.QUIT:
                pygame.quit()
                sys.exit()
            if event.type == pygame.MOUSEBUTTONDOWN:
                if yes_button.collidepoint(event.pos):
                    save_high_score(player_name, score)  # Uložení skóre
                    pygame.quit()
                    sys.exit()
                if no_button.collidepoint(event.pos):
                    return  # Vrátí se do hry


# vytvoření spojení s db
try:
    conn = mariadb.connect(
        user="student12",
        password="spsnet",
        host="dbs.spskladno.cz",
        port=3306,
        database="vyuka12"
    )
    print("Connected to MariaDB!")
    cursor = conn.cursor()
    cursor.execute('CREATE TABLE IF NOT EXISTS `1AUsers` (user_id INT PRIMARY KEY AUTO_INCREMENT, username VARCHAR(255))')
    cursor.execute('CREATE TABLE IF NOT EXISTS `1AScores` (user_id INT PRIMARY KEY, score INT, FOREIGN KEY (user_id) REFERENCES `1AUsers`(user_id) ON DELETE CASCADE ON UPDATE CASCADE)')
    conn.commit()

except mariadb.Error as e:
    print(f"Error connecting to MariaDB: {e}")

# Funkce pro uložení nového skóre do souboru
def save_high_score(player_name, score):
    if score < highest_score:
        #print("Nepodařilo se ti porazit své nejvyšší skóre.")
        return
    try:
        print(f"Gratulujeme! Nové nejvyšší skóre: {score} (ID: #{userID})")
        # Načtení aktuálních skóre
        with open("high_score.txt", "r") as file:
            high_scores = file.readlines()

        # Zkontroluj, zda už hráč má skóre
        player_found = False
        for i, line in enumerate(high_scores):
            name, current_score = line.strip().split(": ")
            if name == player_name:
                if int(current_score) < score:  # Pokud je nové skóre lepší
                    high_scores[i] = f"{player_name}: {score}\n"  # Uprav skóre
                player_found = True
                break

        if not player_found:
            high_scores.append(f"{player_name}: {score}\n")  # Přidej nového hráče

        # Seřaď skóre podle hodnoty (z nejvyššího)
        high_scores.sort(key=lambda x: int(x.split(": ")[1]), reverse=True)
        
        # Udržuj pouze top 10 skóre
        high_scores = high_scores[:10]

        # Ulož výsledky
        with open("high_score.txt", "w") as file:
            file.writelines(high_scores)

        cursor.execute('INSERT INTO `1AScores` (user_id, score) VALUES (?, ?) ON DUPLICATE KEY UPDATE score=?', (userID,score,score)) # Vloží / aktualizuje skóre hráče
        conn.commit()
    except FileNotFoundError:
        # Pokud soubor neexistuje, vytvoř ho
        with open("high_score.txt", "w") as file:
            file.write(f"{player_name}: {score}\n")

def update_user_id(username):
    cursor.execute('SELECT user_id FROM `1AUsers` WHERE username = ?', (username,))
    conn.commit()
    tempId = cursor.fetchone()
    if tempId is None:
        print(f"Inserting {username} into database...")
        cursor.execute('INSERT INTO `1AUsers` (username) VALUES (?)', (username,))
        conn.commit()
        update_user_id(username)
    else:
        global userID
        userID = tempId[0]
        print(f"Logged as {username} ({userID})")

def update_highest_score():
    print(f"Getting high score of user {userID}...")
    cursor.execute('SELECT score FROM `1AScores` WHERE user_id = ?', (userID,))
    conn.commit()
    score = cursor.fetchone()
    if score is None:
        print("Nebylo nalezeno žádné skóre pro uživatele #", userID)
        highest_score = 0
    else:
        highest_score = score[0]
        print(f"High score: {highest_score}")


# Funkce pro hlavní menu
def main_menu():
    global player_name
    input_active = False
    name_input = ""
    play_button_rect = pygame.Rect(width // 2 - 50, height // 2 + 50, 100, 40)
    
    # Písma
    title_font = pygame.font.Font("freesansbold.ttf", 40)
    input_font = pygame.font.Font("freesansbold.ttf", 30)

    cursor_visible = True
    cursor_timer = pygame.USEREVENT + 2  # Událost pro blikající kurzor
    pygame.time.set_timer(cursor_timer, 500)  # Blikání každých 500 ms
    
    while True:
        screen.fill((0, 0, 0))  # Vyčistit obrazovku
        
        # Zobrazit název hry
        title_text = title_font.render("Flappy Bird", True, (255, 255, 255))
        title_rect = title_text.get_rect(center=(width // 2, height // 4))
        screen.blit(title_text, title_rect)

        # Zobrazit vstupní pole pro jméno
        input_text = input_font.render(f"Jméno: {name_input}", True, (255, 255, 255))
        input_rect = input_text.get_rect(center=(width // 2, height // 2 - 30))
        screen.blit(input_text, input_rect)

        # Blikající kurzor
        if cursor_visible:
            cursor_rect = pygame.Rect(input_rect.right, input_rect.top, 10, input_rect.height)
            pygame.draw.rect(screen, (255, 255, 255), cursor_rect)

        # Zobrazit tlačítko "Hrát"
        pygame.draw.rect(screen, (0, 255, 0), play_button_rect)
        play_text = input_font.render("Hrát", True, (0, 0, 0))
        play_rect = play_text.get_rect(center=play_button_rect.center)
        screen.blit(play_text, play_rect)

        for event in pygame.event.get():
            if event.type == pygame.QUIT:
                pygame.quit()
                sys.exit()

            if event.type == pygame.KEYDOWN:
                if event.key == pygame.K_RETURN:  # Stisknutí Enter pro potvrzení
                    player_name = name_input if name_input else "Hráč"  # Default jméno
                    update_user_id(player_name) # Získání ID uživatele
                    update_highest_score() # Získání nejvyššího skóre
                    return
                elif event.key == pygame.K_BACKSPACE:  # Backspace pro smazání
                    name_input = name_input[:-1]
                else:
                    name_input += event.unicode  # Přidat znak do jména

            if event.type == pygame.MOUSEBUTTONDOWN:
                if play_button_rect.collidepoint(event.pos):
                    player_name = name_input if name_input else "Hráč"  # Default jméno 
                    update_user_id(player_name) # Získání ID uživatele
                    update_highest_score() # Získání nejvyššího skóre 
                    return

            if event.type == cursor_timer:
                cursor_visible = not cursor_visible  # Přepnutí viditelnosti kurzoru
        
        pygame.display.update()

# Hra
width, height = 288, 512
clock = pygame.time.Clock()
screen = pygame.display.set_mode((width, height))
pygame.display.set_caption("Flappy Bird")

# Nastavení pozadí a základny
back_img = pygame.image.load("./sprites/pozadi_den.png")
floor_img = pygame.image.load("./sprites/základna.png")
floor_x = 0

# Různé fáze ptáka
bird_up = pygame.image.load("./sprites/redbird-upflap.png")
bird_down = pygame.image.load("./sprites/redbird-downflap.png")
bird_mid = pygame.image.load("./sprites/redbird-midflap.png")
birds = [bird_up, bird_mid, bird_down]
bird_index = 0
bird_flap = pygame.USEREVENT
pygame.time.set_timer(bird_flap, 200)
bird_img = birds[bird_index]
bird_rect = bird_img.get_rect(center=(67, 622 // 2))
bird_movement = 0
gravity = 0.17

# Načítání obrázku trubek
pipe_img = pygame.image.load("./sprites/pipe-green.png")
pipe_height = (200, 450)

# Pro zobrazení trubek
pipes = []
create_pipe = pygame.USEREVENT + 1
pygame.time.set_timer(create_pipe, 1200)

# Zobrazení obrázku Game Over
game_over = False
over_img = pygame.image.load("./sprites/gameover.png").convert_alpha()
over_rect = over_img.get_rect(center=(width // 2, height // 2))

# Nastavení proměnných a písma pro skóre
score = 0
high_score = 0
score_time = True
score_font = pygame.font.Font("freesansbold.ttf", 27)

# Globální proměnná pro hráčovo jméno
player_name = ""

# Před spuštěním hry zkontroluj, zda existuje soubor pro skóre
if not os.path.exists("high_score.txt"):
    with open("high_score.txt", "w") as file:
        file.write("")

# Hlavní herní smyčka
running = True
while running:
    if not player_name:  # Pokud není jméno zadáno, zobrazení menu
        main_menu()  # Zobrazí hlavní menu a čeká na zadání jména
    
    clock.tick(120)

    # Pro zpracování událostí
    for event in pygame.event.get():
        if event.type == pygame.QUIT:  # Událost QUIT
            confirm_exit() # Vyskočí okno pro potvrzení ukončení

        if event.type == pygame.KEYDOWN:  # Událost stisknutí klávesy
            if event.key == pygame.K_SPACE and not game_over:  # Pokud je stisknutá mezerník
                bird_movement = 0
                bird_movement = -5

            if event.key == pygame.K_SPACE and game_over:
                # Po skončení hry se vrátí zpět do hlavního menu pro zadání nového jména
                game_over = False
                pipes = []
                bird_movement = 0
                bird_rect = bird_img.get_rect(center=(67, 622 // 2))
                score_time = True
                score = 0
                player_name = ""  # Vymazání předchozího jména, aby hráč mohl zadat nové
                main_menu()  # Zobrazí hlavní menu pro zadání nového jména

        # Načítání různých fází ptáka
        if event.type == bird_flap:
            bird_index += 1

            if bird_index > 2:
                bird_index = 0

            bird_img = birds[bird_index]
            bird_rect = bird_up.get_rect(center=bird_rect.center)

        # Přidání trubek do seznamu
        if event.type == create_pipe:
            pipes.extend(create_pipes())

    screen.blit(floor_img, (floor_x, 550))
    screen.blit(back_img, (0, 0))

    # Game over
    if not game_over:
        bird_movement += gravity
        bird_rect.centery += bird_movement
        rotated_bird = pygame.transform.rotozoom(bird_img, bird_movement * -5, 1)

        if bird_rect.top < 5 or bird_rect.bottom >= 490: 
            game_over = True  # Nastavení game_over na True při kolizi s trubkou
            main_menu()  # Po kolizi se zobrazí hlavní menu
            continue  # Aby se vše znovu inicializovalo po kolizi

        screen.blit(rotated_bird, bird_rect)
        pipe_animation()
        score_update()
        draw_score("game_on")
        
    elif game_over:
        screen.blit(over_img, over_rect)
        draw_score("game_over")
        floor_x = 0

    # Pohyb základny
    floor_x -= 3
    if floor_x < -448:
        floor_x = 0

    draw_floor()

    # Aktualizace herního okna
    pygame.display.update()

# Konec
pygame.quit()
sys.exit()

 