document.addEventListener('DOMContentLoaded', function() {

    // --- ADICIONAR AO CARRINHO COM AJAX ---
    // Delegação de eventos para todos os formulários com a classe 'form-add-to-cart'
    document.body.addEventListener('submit', function(event) {
        if (event.target.matches('.form-add-to-cart')) {
            event.preventDefault(); // Impede o envio normal do formulário

            const form = event.target;
            const formData = new FormData(form);

            fetch('carrinho_acoes.php', {
                method: 'POST',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest' // Identifica a requisição como AJAX
                },
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Atualiza o contador do carrinho no header
                    const cartBadge = document.querySelector('.navbar-nav .badge');
                    if (cartBadge) {
                        cartBadge.textContent = data.cart_count;
                    }

                    // Exibe o SweetAlert de sucesso
                    Swal.fire({
                        icon: 'success',
                        title: 'Adicionado!',
                        text: data.message,
                        toast: true, // Mostra um alerta menor
                        position: 'top-end', // No canto superior direito
                        showConfirmButton: false,
                        timer: 2000, // Fecha automaticamente após 2 segundos
                        timerProgressBar: true
                    });
                } else {
                    // Exibe alerta de erro se a resposta indicar falha
                    Swal.fire({
                        icon: 'error',
                        title: 'Oops...',
                        text: data.message || 'Ocorreu um erro.'
                    });
                }
            })
            .catch(error => {
                console.error('Erro na requisição:', error);
                Swal.fire({
                    icon: 'error',
                    title: 'Erro de Conexão',
                    text: 'Não foi possível adicionar o produto ao carrinho.'
                });
            });
        }
    });

// --- CONFIRMAÇÃO PARA REMOVER UM ITEM ESPECÍFICO ---
document.body.addEventListener('click', function(event) {
    // Procura por um clique em um elemento ou pai com a classe .btn-remover-item
    const removeButton = event.target.closest('.btn-remover-item');
    if (removeButton) {
        event.preventDefault(); // Previne o clique padrão do link
        const url = removeButton.href;

        Swal.fire({
            title: 'Remover item?',
            text: "Tem certeza que deseja remover este item do carrinho?",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Sim, remover!',
            cancelButtonText: 'Cancelar'
        }).then((result) => {
            if (result.isConfirmed) {
                // Se o usuário confirmar, redireciona para a URL de remover o item
                window.location.href = url;
            }
        });
    }
});

});