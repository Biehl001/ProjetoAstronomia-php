    </main>

    <!-- Footer -->
    <footer class="footer">
        <div class="container">
            <div class="footer-grid">
                <div class="footer-section">
                    <h3><i class="fas fa-meteor"></i> Cosmos<strong>News</strong></h3>
                    <p>Seu portal de notícias sobre o universo. Explorando as fronteiras do cosmos, uma notícia de cada vez.</p>
                    <div class="footer-social">
                        <a href="#"><i class="fab fa-twitter"></i></a>
                        <a href="#"><i class="fab fa-instagram"></i></a>
                        <a href="#"><i class="fab fa-youtube"></i></a>
                        <a href="#"><i class="fab fa-discord"></i></a>
                    </div>
                </div>
                <div class="footer-section">
                    <h4>Navegação</h4>
                    <ul>
                        <li><a href="/ProjetoAstronomia-php/">Início</a></li>
                        <li><a href="/ProjetoAstronomia-php/categorias.php">Categorias</a></li>
                        <li><a href="/ProjetoAstronomia-php/busca.php">Buscar</a></li>
                    </ul>
                </div>
                <div class="footer-section">
                    <h4>Categorias</h4>
                    <ul>
                        <?php foreach ($categoriasMenu as $cat): ?>
                        <li>
                            <a href="/ProjetoAstronomia-php/categorias.php?id=<?= $cat['id'] ?>">
                                <?= htmlspecialchars($cat['nome']) ?>
                            </a>
                        </li>
                        <?php endforeach; ?>
                    </ul>
                </div>
                <div class="footer-section">
                    <h4>Conta</h4>
                    <ul>
                        <?php if (isLoggedIn()): ?>
                            <li><a href="/ProjetoAstronomia-php/pages/perfil.php">Meu Perfil</a></li>
                            <li><a href="/ProjetoAstronomia-php/pages/minhas-noticias.php">Minhas Notícias</a></li>
                            <li><a href="/ProjetoAstronomia-php/pages/noticia-form.php">Nova Notícia</a></li>
                        <?php else: ?>
                            <li><a href="/ProjetoAstronomia-php/login.php">Entrar</a></li>
                            <li><a href="/ProjetoAstronomia-php/cadastro.php">Cadastrar</a></li>
                        <?php endif; ?>
                    </ul>
                </div>
            </div>
            <div class="footer-bottom">
                <p>&copy; <?= date('Y') ?> CosmosNews - Portal de Astronomia. Projeto acadêmico.</p>
                <p class="footer-quote"><i class="fas fa-star"></i> "O universo não é apenas mais estranho do que supomos, é mais estranho do que podemos supor." - J.B.S. Haldane</p>
            </div>
        </div>
    </footer>

    <script src="/ProjetoAstronomia-php/assets/js/main.js"></script>
</body>
</html>
