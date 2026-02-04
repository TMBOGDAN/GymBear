<!DOCTYPE html>
<html lang="ro">
<head>
    <meta charset="UTF-8">
    <title>Despre GymBear</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <style> 

        body {
            background-color: #0f0f0f;
            color: #f1f1f1; 
            font-family: Arial, Helvetica, sans-serif;
            font-size: 18px;
            line-height: 1.75;
            margin: 0;
            padding: 0;
        }

        .container {
            max-width: 900px;
            margin: 0 auto;
            padding: 80px 20px;
        }

        .main {
            background-color: transparent;
            padding: 10px;
        }

        p {
            margin-bottom: 10px;
            text-align: left;
        }

        section-p{
            margin:0px;
        }

        h1 {
            font-size: 28px;
            margin-bottom: 15px;
            color: #ff0000;
            text-align: center;
        }

        h2 {
            font-size: 24px;
            margin-bottom: 15px;
            color: #ff0000;
            text-align: left;
        }

        h3 {
            font-size: 18px;
            margin: 15px 0 10px;
            color: #ffffff;
            text-align: left;
        }

        ul, ol {
            padding-left: 30px;
            margin-bottom: 15px;
        }

        li {
            margin-bottom: 10px;
            text-align: left;
        }

        .er-diagram {
            max-width: 100%;
            height: 400px;
            display: block;
            margin: 20px auto;
            border: 1px solid #ccc;
            padding: 6px;
            background: #250000;
            box-shadow: 0 6px 18px rgba(0,0,0,0.15);
        }

        .er-caption {
            font-size: 11px;
            color: #bbbbbb;
            text-align: center;
            margin-top: 6px;
        }
        .p{
            text-align: left;

        }


        footer {
            text-align: center;
            font-size: 14px;
            color: #aaaaaa;
            padding: 20px 0;
            border-top: 1px solid red;
            margin-top: 80px;
        }
    </style>
    
     <link rel="stylesheet" href="/style/style.css">

</head> 

<body>
    <?php include '../includes/header.php'; ?> 

    <main class="container">

        <h1>Despre aplicatia GymBear</h1>

        <!-- 1. DESCRIEREA APLICATIEI -->
        <section>
            <h2>1. Descrierea aplicatiei web</h2>
            <p>
                <strong>GymBear</strong> este o aplicatie web dedicata gestionarii unui centru de fitness.
                Aplicatia ofera functionalitati pentru administrarea utilizatorilor, abonamentelor,
                antrenorilor si sedintelor de antrenament, precum si un modul de contact.
            </p>
            <p>
                Scopul proiectului este unul educational si urmareste aplicarea notiunilor studiate
                in cadrul cursului: arhitectura web, lucrul cu baze de date relationale,
                autentificare, securitate si structurarea corecta a unei aplicatii web.
            </p>
        </section>

        <!-- 2. ARHITECTURA -->
        <section>
            <h2>2. Arhitectura aplicatiei</h2>
            <p>
                Aplicatia este realizata folosind o arhitectura de tip <strong>client–server</strong>.
                Utilizatorul interactioneaza cu aplicatia prin intermediul browserului,
                iar cererile sunt procesate de serverul PHP, care comunica cu baza de date MySQL.
            </p>

            <h3>Componente principale</h3>
            <ul>
                <li><strong>Frontend:</strong> HTML, CSS – afisare si interactiune</li>
                <li><strong>Backend:</strong> PHP – logica aplicatie si procesare date</li>
                <li><strong>Baza de date:</strong> MySQL – stocare date</li>
            </ul>

            <h3>Roluri in aplicatie</h3>
            <ul>
                <li><strong>Utilizator</strong> – client al salii de fitness</li>
                <li><strong>Administrator</strong> – gestioneaza datele aplicatiei</li>
            </ul>

            <h3>Entitati principale</h3>
            <ul>
                <li><code>accounts</code> – utilizatori si administratori</li>
                <li><code>abonamente</code> – tipuri de abonamente</li>
                <li><code>antrenori</code> – antrenori disponibili</li>
                <li><code>sedinte</code> – sedinte de antrenament</li>
                <li><code>contact_mesaje</code> – mesaje de contact</li>
                <li><code>sessions</code> – sesiuni PHP</li>
            </ul>

            <h3>Relatii intre entitati</h3>
            <ul>
                <li>Un utilizator poate avea mai multe sedinte</li>
                <li>Un antrenor poate sustine mai multe sedinte</li>
                <li>Un abonament poate fi asociat mai multor sedinte</li>
            </ul>
        </section>

        <!-- 3. TEHNOLOGII -->
        <section>
            <h2>3. Tehnologii utilizate</h2>
            <ul>
                <li>PHP (backend)</li>
                <li>MySQL (baza de date relationala)</li>
                <li>HTML5 / CSS3</li>
                <li>Composer</li>
                <li>PHPMailer pentru trimitere e-mail</li>
            </ul>
        </section>

        <!-- 4. BAZA DE DATE -->
        <section>
            <h2>4. Schema bazei de date (ER si UML Diagram)</h2>
            <p>
                Diagrama de mai jos prezinta structura bazei de date si relatiile intre tabelele
                utilizate in aplicatia GymBear.
            </p>

            <img src="/resources/images/uml.png"
                alt="UML GymBear"
                class="er-diagram">
            <img src="/resources/images/diagrama.png"  class="er-diagram">

            <div class="er-caption">
                Tabele: accounts, abonamente, antrenori, sedinte, contact_mesaje, sessions
            </div>
        </section>

        <!-- 5. FLUXURI -->
        <section>
            <h2>5. Fluxuri principale (Use Cases)</h2>
            <ol>
                <li>Inregistrare si autentificare utilizator</li>
                <li>Vizualizare abonamente</li>
                <li>Rezervare sedinta</li>
                <li>Administrare date (admin)</li>
                <li>Trimitere mesaj de contact</li>
            </ol>
        </section>

        <!-- 6. SECURITATE -->
        <section>
            <h2>6. Masuri de securitate</h2>
            <ul>
                <li>Parole stocate folosind <code>password_hash</code></li>
                <li>Sesiuni PHP</li>
                <li>Token CSRF pentru formulare</li>
                <li>Prepared statements</li>
                <li>Sanitizarea datelor afisate</li>
            </ul>
        </section>

        <!-- 7. CONCLUZII -->
        <section>
            <h2>7. Concluzii si perspective de dezvoltare</h2>
            <p>
                Aplicatia GymBear este o solutie educationala ce integreaza functionalitati esentiale
                pentru gestionarea unui centru de fitness. Pe viitor, se poate extinde cu:
            </p>
            <ul>
                <li>Notificari prin e-mail pentru sedinte</li>
                <li>Module avansate de statistici si rapoarte</li>
                <li>Aplicatie mobila dedicata</li>
                <li>Optimizari si imbunatatiri de securitate</li>
            </ul>
        </section>

    </main>

    <?php include '../includes/footer.php'; ?> 

</body>
</html>
