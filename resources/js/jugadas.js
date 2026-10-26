document.addEventListener('DOMContentLoaded', () => {
    // 1. Elementos Base
    const tabs = document.querySelectorAll('.jugada-tab');
    const sections = document.querySelectorAll('.jugada-form-section');
    const formJugada = document.getElementById('form-jugada');
    
    // Elementos del Resumen / Carrito
    const resumenMonto = document.getElementById('resumen-monto');
    const resumenSeleccion = document.getElementById('resumen-seleccion');
    const btnAddCart = document.getElementById('btn-add-cart');
    
    const cartList = document.getElementById('cart-list');
    const cartTotalAmount = document.getElementById('cart-total-amount');
    const btnSubmit = document.getElementById('btn-submit-cart');
    const hiddenCartInput = document.getElementById('cart_data');

    // Inputs de formularios
    const inputQuinielaNum = document.getElementById('numero');
    const inputQuinielaJur = document.getElementById('jurisdiccion');
    const inputQuinielaImp = document.getElementById('importe');
    const inputLotoPlusPlus = document.getElementById('numero_plus');
    const selectRangoPos = document.getElementById('rango_posiciones');
    const btnClearPos = document.getElementById('btn-clear-posiciones');

    // 2. Precios y Estado
    const preciosData = document.getElementById('precios-data');
    const PRECIOS = {
        quini6: parseFloat(preciosData.dataset.quini6),
        lotoplus: parseFloat(preciosData.dataset.lotoplus),
        loto5: parseFloat(preciosData.dataset.loto5),
        poceada: parseFloat(preciosData.dataset.poceada)
    };

    const state = {
        modalidad: 'quiniela',
        selecciones: {
            quiniela: [], // Para guardar posiciones 1-20
            quini6: [],
            lotoplus: [],
            loto5: [],
            poceada: []
        },
        max: {
            quiniela: 20,
            quini6: 6,
            lotoplus: 6,
            loto5: 5,
            poceada: 8
        },
        cart: []
    };

    const formatCurrency = (amount) => amount.toLocaleString('es-AR', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
    
    // 4. Lógica de Tabs
    tabs.forEach(tab => {
        tab.addEventListener('click', () => {
            const target = tab.dataset.target;
            
            tabs.forEach(t => {
                t.classList.remove('active');
                t.setAttribute('aria-selected', 'false');
            });
            tab.classList.add('active');
            tab.setAttribute('aria-selected', 'true');

            sections.forEach(s => s.style.display = 'none');
            document.getElementById(`panel-${target}`).style.display = 'block';

            state.modalidad = target;
            updateResumen();
            checkValidity();
        });
    });

    // 5. Grillas Interactivas
    const renderGrid = (id, start, end, format) => {
        const container = document.getElementById(`grid-${id}`);
        if (!container) return;
        
        container.innerHTML = '';
        
        for (let i = start; i <= end; i++) {
            const btn = document.createElement('button');
            btn.type = 'button';
            btn.className = 'numero-btn';
            
            const numText = format ? i.toString().padStart(2, '0') : i.toString();
            btn.textContent = numText;
            btn.dataset.num = i;
            
            btn.addEventListener('click', () => toggleNumero(id, i, btn));
            container.appendChild(btn);
        }
    };

    let toggleNumero = (mod, num, btn) => {
        const max = state.max[mod];
        const selected = state.selecciones[mod];
        
        const index = selected.indexOf(num);
        if (index > -1) {
            selected.splice(index, 1);
            btn.classList.remove('selected');
        } else {
            if (selected.length < max) {
                selected.push(num);
                btn.classList.add('selected');
            }
        }
        
        // Sort for numeric order just in case
        selected.sort((a, b) => a - b);
        
        updateGridUI(mod);
        updateResumen();
        checkValidity();
    };

    const updateGridUI = (mod) => {
        const max = state.max[mod];
        const count = state.selecciones[mod].length;
        const container = document.getElementById(mod === 'quiniela' ? 'grid-posiciones-quiniela' : `grid-${mod}`);
        if(!container) return;
        const buttons = container.querySelectorAll('.numero-btn');
        
        buttons.forEach(btn => {
            const num = parseInt(btn.dataset.num);
            if (state.selecciones[mod].includes(num)) {
                btn.classList.add('selected');
            } else {
                btn.classList.remove('selected');
            }

            if (!btn.classList.contains('selected') && count >= max) {
                btn.disabled = true;
            } else {
                btn.disabled = false;
            }
        });
    };

    const resetSelections = () => {
        state.selecciones = { quiniela: [], quini6: [], lotoplus: [], loto5: [], poceada: [] };
        
        ['quiniela', 'quini6', 'lotoplus', 'loto5', 'poceada'].forEach(mod => {
            updateGridUI(mod);
        });

        if (inputQuinielaNum) inputQuinielaNum.value = '';
        if (inputQuinielaJur) inputQuinielaJur.value = '';
        if (inputQuinielaImp) inputQuinielaImp.value = '';
        if (inputLotoPlusPlus) inputLotoPlusPlus.value = '';
        if (selectRangoPos) selectRangoPos.value = '';

        updateResumen();
        checkValidity();
    };

    // Render all grids
    renderGrid('posiciones-quiniela', 1, 20, false);
    renderGrid('quini6', 0, 45, true);
    renderGrid('lotoplus', 0, 45, true);
    renderGrid('loto5', 0, 36, true);
    renderGrid('poceada', 0, 99, true);

    // Quiniela Range Logic
    if (selectRangoPos) {
        selectRangoPos.addEventListener('change', (e) => {
            const limit = parseInt(e.target.value);
            if (!limit) return;
            
            state.selecciones.quiniela = [];
            for (let i = 1; i <= limit; i++) {
                state.selecciones.quiniela.push(i);
            }
            updateGridUI('quiniela');
            updateResumen();
            checkValidity();
        });
    }

    if (btnClearPos) {
        btnClearPos.addEventListener('click', () => {
            state.selecciones.quiniela = [];
            if(selectRangoPos) selectRangoPos.value = '';
            updateGridUI('quiniela');
            updateResumen();
            checkValidity();
        });
    }

    // 6. Generador de Jugadas Aleatorias (Quick Pick)
    const getRandomInt = (min, max) => Math.floor(Math.random() * (max - min + 1)) + min;

    const getRandomUniqueNumbers = (count, min, max) => {
        const pool = [];
        for (let i = min; i <= max; i++) pool.push(i);
        const result = [];
        for (let i = 0; i < count; i++) {
            const idx = Math.floor(Math.random() * pool.length);
            result.push(pool.splice(idx, 1)[0]);
        }
        return result.sort((a, b) => a - b);
    };

    const generarJugadaAzar = (mod) => {
        if (mod === 'quiniela') {
            const num = getRandomInt(10, 9999).toString();
            if (inputQuinielaNum) inputQuinielaNum.value = num;

            const jurisdicciones = ['nacion', 'provincia', 'santa_fe', 'cordoba'];
            if (inputQuinielaJur) {
                inputQuinielaJur.value = jurisdicciones[Math.floor(Math.random() * jurisdicciones.length)];
            }

            const importes = [500, 1000, 1500, 2000];
            if (inputQuinielaImp) {
                inputQuinielaImp.value = importes[Math.floor(Math.random() * importes.length)];
            }

            const rangos = [1, 5, 10, 20];
            const elegido = rangos[Math.floor(Math.random() * rangos.length)];
            if (selectRangoPos) selectRangoPos.value = elegido.toString();

            state.selecciones.quiniela = [];
            for (let i = 1; i <= elegido; i++) {
                state.selecciones.quiniela.push(i);
            }
            updateGridUI('quiniela');
        } else if (mod === 'quini6') {
            state.selecciones.quini6 = getRandomUniqueNumbers(6, 0, 45);
            updateGridUI('quini6');
        } else if (mod === 'lotoplus') {
            state.selecciones.lotoplus = getRandomUniqueNumbers(6, 0, 45);
            updateGridUI('lotoplus');
            if (inputLotoPlusPlus) {
                inputLotoPlusPlus.value = getRandomInt(0, 9);
            }
        } else if (mod === 'loto5') {
            state.selecciones.loto5 = getRandomUniqueNumbers(5, 0, 36);
            updateGridUI('loto5');
        } else if (mod === 'poceada') {
            state.selecciones.poceada = getRandomUniqueNumbers(8, 0, 99);
            updateGridUI('poceada');
        }

        updateResumen();
        checkValidity();
    };

    // 7. Form Inputs & Validaciones
    const updateResumen = () => {
        const mod = state.modalidad;
        let costo = 0;
        let status = '';

        if (mod === 'quiniela') {
            const imp = parseFloat(inputQuinielaImp.value) || 0;
            const countPos = state.selecciones.quiniela.length;
            costo = countPos > 0 ? (imp * countPos) : 0;
            status = costo > 0 ? `Quiniela: \$${imp} en ${countPos} posiciones` : 'Configurá tu jugada de Quiniela.';
        } else {
            const count = state.selecciones[mod].length;
            const max = state.max[mod];
            costo = count === max ? PRECIOS[mod] : 0;
            status = `${count} / ${max} números seleccionados.`;
            
            if (mod === 'lotoplus' && count === max) {
                if (inputLotoPlusPlus.value === '') {
                    status += ' (Falta N° Plus)';
                } else {
                    status += ` (N° Plus: ${inputLotoPlusPlus.value})`;
                }
            }
        }

        resumenMonto.textContent = formatCurrency(costo);
        resumenSeleccion.textContent = status;
    };

    const checkValidity = () => {
        const mod = state.modalidad;
        let valid = false;

        if (mod === 'quiniela') {
            const num = inputQuinielaNum.value;
            const countPos = state.selecciones.quiniela.length;
            const jur = inputQuinielaJur.value;
            const imp = parseFloat(inputQuinielaImp.value) || 0;
            
            const numRegex = /^\d{1,4}$/;
            if (numRegex.test(num) && countPos > 0 && jur && imp > 0) {
                valid = true;
            }
        } else {
            const count = state.selecciones[mod].length;
            const max = state.max[mod];
            
            if (count === max) {
                if (mod === 'lotoplus') {
                    const plus = parseInt(inputLotoPlusPlus.value);
                    if (!isNaN(plus) && plus >= 0 && plus <= 9) valid = true;
                } else {
                    valid = true;
                }
            }
        }

        btnAddCart.disabled = !valid;
    };

    [inputQuinielaNum, inputQuinielaJur, inputQuinielaImp, inputLotoPlusPlus].forEach(el => {
        if (el) {
            el.addEventListener('input', () => { updateResumen(); checkValidity(); });
            el.addEventListener('change', () => { updateResumen(); checkValidity(); });
        }
    });

    // Override toggleNumero to unselect range dropdown if manual selection happens for Quiniela
    const originalToggle = toggleNumero;
    toggleNumero = (mod, num, btn) => {
        if (mod === 'posiciones-quiniela') {
            mod = 'quiniela';
            if(selectRangoPos) selectRangoPos.value = '';
        }
        
        const max = state.max[mod];
        const selected = state.selecciones[mod];
        const index = selected.indexOf(num);
        if (index > -1) {
            selected.splice(index, 1);
            btn.classList.remove('selected');
        } else {
            if (selected.length < max) {
                selected.push(num);
                btn.classList.add('selected');
            }
        }
        selected.sort((a, b) => a - b);
        updateGridUI(mod);
        updateResumen();
        checkValidity();
    };

    // 7. Lógica del Carrito
    const renderCart = () => {
        cartList.innerHTML = '';
        let total = 0;

        if (state.cart.length === 0) {
            cartList.innerHTML = '<div style="color: var(--color-muted); font-size: 0.85rem; padding: 1rem 0; text-align: center;">La boleta está vacía.</div>';
            cartTotalAmount.textContent = '0.00';
            btnSubmit.disabled = true;
            return;
        }

        state.cart.forEach((item, index) => {
            total += item.costo;
            
            const div = document.createElement('div');
            div.style.cssText = 'display: flex; justify-content: space-between; align-items: center; padding: 0.75rem 0; border-bottom: 1px solid var(--color-border); font-size: 0.85rem;';
            
            let label = '';
            if (item.modalidad === 'quiniela') {
                label = `<strong>Quiniela (${item.jurisdiccion})</strong><br>Nº ${item.numero} | Posiciones: ${item.posiciones.join(', ')}`;
            } else if (item.modalidad === 'quini6') {
                label = `<strong>Quini 6</strong><br>${item.numeros.join(', ')}`;
            } else if (item.modalidad === 'lotoplus') {
                label = `<strong>Loto Plus</strong><br>${item.numeros.join(', ')} (Plus: ${item.numero_plus})`;
            } else if (item.modalidad === 'loto5') {
                label = `<strong>Loto 5</strong><br>${item.numeros.join(', ')}`;
            } else if (item.modalidad === 'poceada') {
                label = `<strong>Poceada</strong><br>${item.numeros.join(', ')}`;
            }

            div.innerHTML = `
                <div style="flex: 1;">${label}</div>
                <div style="font-weight: bold; color: var(--color-gold); margin: 0 1rem;">$${formatCurrency(item.costo)}</div>
                <button type="button" class="btn btn-ghost btn-sm" style="color: #e53e3e; padding: 0.25rem 0.5rem;" data-index="${index}">✖</button>
            `;
            cartList.appendChild(div);
        });

        cartList.querySelectorAll('button').forEach(btn => {
            btn.addEventListener('click', (e) => {
                const idx = parseInt(e.currentTarget.dataset.index);
                state.cart.splice(idx, 1);
                renderCart();
            });
        });

        cartTotalAmount.textContent = formatCurrency(total);
        btnSubmit.disabled = false;
        hiddenCartInput.value = JSON.stringify(state.cart);
    };

    btnAddCart.addEventListener('click', () => {
        const mod = state.modalidad;
        let item = { modalidad: mod };

        if (mod === 'quiniela') {
            item.numero = inputQuinielaNum.value;
            item.posiciones = [...state.selecciones.quiniela];
            item.jurisdiccion = inputQuinielaJur.value;
            item.importe = parseFloat(inputQuinielaImp.value);
            item.costo = item.importe * item.posiciones.length;
        } else {
            item.numeros = [...state.selecciones[mod]];
            item.costo = PRECIOS[mod];
            
            if (mod === 'lotoplus') {
                item.numero_plus = parseInt(inputLotoPlusPlus.value);
            }
        }

        state.cart.push(item);
        renderCart();
        resetSelections();
    });

    // Botones de juego al azar
    document.querySelectorAll('.btn-random').forEach(btn => {
        btn.addEventListener('click', (e) => {
            const mod = e.currentTarget.dataset.mod;
            generarJugadaAzar(mod);
        });
    });

    updateResumen();
    checkValidity();
    renderCart();
});
