             </div>
        </main>
    </div>
    <div id="stock-alert-modal" class="modal-personalizado" style="display: none;">
    </div>

<div id="pdf-preview-modal" class="modal-personalizado" style="display: none;">
    <div class="modal-contenido-pdf">
        
        <div class="pdf-header">
            <h3>Vista Previa de Factura</h3>
            <div>
                <button id="btn-descargar-pdf-modal" class="btn btn-primary">
                    <i class="fas fa-download"></i> Descargar
                </button>
                <span class="cerrar-modal" onclick="ocultarPdfModal()">&times;</span>
            </div>
        </div>
        
        <iframe id="pdf-iframe" src=""></iframe>
        
    </div>
</div>
<script>
document.addEventListener('DOMContentLoaded', function() {
    
    function setupPaginator(tableId, paginatorId, rowsPerPage = 5) {
        const table = document.getElementById(tableId);
        if (!table) return; 

        const paginatorContainer = document.getElementById(paginatorId);
        if (!paginatorContainer) return; 
        
        const tbody = table.querySelector('tbody');
        if (!tbody) return; 
        
        const rows = Array.from(tbody.querySelectorAll('tr'));
        
        if (rows.length === 0 || (rows.length === 1 && rows[0].querySelectorAll('td').length === 1)) {
            paginatorContainer.style.display = 'none';
            return;
        }

        const pageCount = Math.ceil(rows.length / rowsPerPage);
        let currentPage = 1;

        if (pageCount <= 1) {
            paginatorContainer.style.display = 'none';
            return;
        }

        function displayRows(page) {
            currentPage = page;
            rows.forEach(row => row.style.display = 'none');
            const start = (page - 1) * rowsPerPage;
            const end = start + rowsPerPage;
            rows.slice(start, end).forEach(row => row.style.display = 'table-row');
        }

        function setupButtons() {
            paginatorContainer.innerHTML = ''; 
            
            // Botón "Anterior" con icono
            let prevButton = document.createElement('button');
            prevButton.innerHTML = '&laquo;';
            prevButton.className = 'btn btn-secondary btn-anterior';
            prevButton.disabled = (currentPage === 1);
            prevButton.addEventListener('click', () => {
                if (currentPage > 1) {
                    displayRows(currentPage - 1);
                    setupButtons();
                }
            });
            paginatorContainer.appendChild(prevButton);

            // Indicador de página
            let pageIndicator = document.createElement('span');
            pageIndicator.innerText = `Página ${currentPage} de ${pageCount}`;
            pageIndicator.className = 'page-indicator';
            paginatorContainer.appendChild(pageIndicator);
            
            // Botón "Siguiente" con icono
            let nextButton = document.createElement('button');
            nextButton.innerHTML = '&raquo;'; 
            nextButton.className = 'btn btn-primary btn-siguiente';
            nextButton.disabled = (currentPage === pageCount);
            nextButton.addEventListener('click', () => {
                if (currentPage < pageCount) {
                    displayRows(currentPage + 1);
                    setupButtons();
                }
            });
            paginatorContainer.appendChild(nextButton);
        }
        
        displayRows(1); 
        setupButtons(); 
    }

    // --- Inicializar los paginadores ---
    setupPaginator('tabla-historial-ventas', 'paginador-ventas', 5);
    setupPaginator('tabla-historial-tasas', 'paginador-tasas', 3);
});
</script>
</body>
</html>          

