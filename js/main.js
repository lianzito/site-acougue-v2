document.addEventListener('DOMContentLoaded', function () {

    document.body.addEventListener('submit', function (event) {
        if (event.target.matches('.form-add-to-cart')) {
            event.preventDefault();
            const form = event.target;
            const formData = new FormData(form);

            fetch('carrinho_acoes.php', {
                method: 'POST',
                headers: { 'X-Requested-With': 'XMLHttpRequest' },
                body: formData
            })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        const badge = document.querySelector('.navbar-nav .badge');
                        if (badge) badge.textContent = data.cart_count;
                        Swal.fire({
                            icon: 'success',
                            title: 'Adicionado!',
                            text: data.message,
                            toast: true,
                            position: 'top-end',
                            showConfirmButton: false,
                            timer: 2000,
                            timerProgressBar: true
                        });
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Oops...',
                            text: data.message || 'Ocorreu um erro.'
                        });
                    }
                })
                .catch(() => {
                    Swal.fire({
                        icon: 'error',
                        title: 'Erro de Conexão',
                        text: 'Não foi possível adicionar o produto ao carrinho.'
                    });
                });
        }
    });

    document.body.addEventListener('click', function (event) {
        const btn = event.target.closest('.btn-remover-item');
        if (!btn) return;
        event.preventDefault();

        Swal.fire({
            title: 'Remover item?',
            text: 'Tem certeza que deseja remover este item do carrinho?',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Sim, remover!',
            cancelButtonText: 'Cancelar'
        }).then(result => {
            if (result.isConfirmed) window.location.href = btn.href;
        });
    });
    const observer = new IntersectionObserver((entries, obs) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.classList.add('in-view');
                obs.unobserve(entry.target);
            }
        });
    }, { threshold: 0.1 });

    document.querySelectorAll('.produto-card').forEach(card => {
        observer.observe(card);
    });
});

window.addEventListener('scroll', () => {
    document.querySelector('header')
        .classList.toggle('scrolled', window.scrollY > 10);
});
