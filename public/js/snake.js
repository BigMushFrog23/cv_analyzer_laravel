// ============================================================
//  Mini-jeu Snake — affiché pendant l'analyse du CV
//  Petit, pixelisé. Démarre via window.startSnake() au submit.
// ============================================================
(function () {
  let started = false;

  window.startSnake = function () {
    if (started) return; // ne pas relancer deux fois
    const wait   = document.getElementById('snakeWait');
    const canvas = document.getElementById('snakeCanvas');
    if (!wait || !canvas) return;
    started = true;

    wait.hidden = false;
    wait.scrollIntoView({ behavior: 'smooth', block: 'center' });

    const ctx     = canvas.getContext('2d');
    const CELL    = 10;
    const COLS    = canvas.width  / CELL; // 16
    const ROWS    = canvas.height / CELL; // 16
    const scoreEl = document.getElementById('snakeScore');

    let snake, dir, nextDir, food, score, dead, running = false;

    function reset() {
      snake   = [{ x: 8, y: 8 }, { x: 7, y: 8 }, { x: 6, y: 8 }];
      dir     = { x: 1, y: 0 };
      nextDir = dir;
      score   = 0;
      dead    = false;
      placeFood();
      updateScore();
    }

    function placeFood() {
      let p;
      do {
        p = { x: Math.floor(Math.random() * COLS), y: Math.floor(Math.random() * ROWS) };
      } while (snake.some(s => s.x === p.x && s.y === p.y));
      food = p;
    }

    function updateScore() {
      if (scoreEl) scoreEl.textContent = score;
    }

    function step() {
      if (dead) return;
      dir = nextDir;
      const head = { x: snake[0].x + dir.x, y: snake[0].y + dir.y };

      // Collision avec un mur ou avec soi-même → game over
      const hitWall = head.x < 0 || head.y < 0 || head.x >= COLS || head.y >= ROWS;
      const hitSelf = snake.some(s => s.x === head.x && s.y === head.y);
      if (hitWall || hitSelf) {
        dead = true;
        draw();
        return;
      }

      snake.unshift(head);
      if (head.x === food.x && head.y === food.y) {
        score++;
        updateScore();
        placeFood();
      } else {
        snake.pop();
      }
      draw();
    }

    function draw() {
      ctx.fillStyle = '#0e1117';
      ctx.fillRect(0, 0, canvas.width, canvas.height);

      // Pomme
      ctx.fillStyle = '#ef4444';
      ctx.fillRect(food.x * CELL, food.y * CELL, CELL, CELL);

      // Serpent (tête plus claire)
      for (let i = 0; i < snake.length; i++) {
        ctx.fillStyle = i === 0 ? '#22c55e' : '#16a34a';
        ctx.fillRect(snake[i].x * CELL + 1, snake[i].y * CELL + 1, CELL - 2, CELL - 2);
      }

      if (dead) {
        ctx.fillStyle = 'rgba(0,0,0,0.65)';
        ctx.fillRect(0, 0, canvas.width, canvas.height);
        ctx.fillStyle = '#fff';
        ctx.font = '11px monospace';
        ctx.textAlign = 'center';
        ctx.fillText('Game Over', canvas.width / 2, canvas.height / 2 - 4);
        ctx.fillText('Espace = rejouer', canvas.width / 2, canvas.height / 2 + 12);
      }
    }

    // Écran d'attente : le jeu ne démarre qu'à la première touche / clic
    function drawIdle() {
      ctx.fillStyle = '#0e1117';
      ctx.fillRect(0, 0, canvas.width, canvas.height);
      ctx.fillStyle = '#fff';
      ctx.font = '11px monospace';
      ctx.textAlign = 'center';
      ctx.fillText('Appuie sur une', canvas.width / 2, canvas.height / 2 - 10);
      ctx.fillText('touche pour', canvas.width / 2, canvas.height / 2 + 3);
      ctx.fillText('jouer !', canvas.width / 2, canvas.height / 2 + 16);
    }

    function begin() {
      if (running) return;
      running = true;
      reset();
      draw();
      setInterval(step, 130);
    }

    const KEYS = {
      ArrowUp:    { x: 0,  y: -1 }, ArrowDown:  { x: 0,  y: 1 },
      ArrowLeft:  { x: -1, y: 0 },  ArrowRight: { x: 1,  y: 0 },
      // AZERTY (clavier français)
      z: { x: 0, y: -1 }, s: { x: 0, y: 1 }, q: { x: -1, y: 0 }, d: { x: 1, y: 0 },
    };

    document.addEventListener('keydown', (e) => {
      // Première touche → on lance la partie
      if (!running) {
        e.preventDefault();
        begin();
        return;
      }
      if (e.code === 'Space') {
        if (dead) reset();
        e.preventDefault();
        return;
      }
      const nd = KEYS[e.key];
      if (!nd) return;
      e.preventDefault(); // empêche le scroll de la page avec les flèches
      // Interdire le demi-tour direct
      if (nd.x === -dir.x && nd.y === -dir.y) return;
      nextDir = nd;
    });

    // Démarrage au clic / tap aussi (pratique sur mobile)
    canvas.addEventListener('click', () => { if (!running) begin(); });

    drawIdle();
  };
})();
