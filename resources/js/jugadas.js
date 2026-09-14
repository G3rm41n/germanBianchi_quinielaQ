document.addEventListener('DOMContentLoaded', () => {
    // 1. Elementos
    const tabs = document.querySelectorAll('.jugada-tab');
    const sections = document.querySelectorAll('.jugada-form-section');
    const inputModalidad = document.getElementById('input_modalidad');
    const resumenMonto = document.getElementById('resumen-monto');
    const resumenSeleccion = document.getElementById('resumen-seleccion');
    const btnSubmit = document.getElementById('btn-submit');
    const formJugada = document.getElementById('form-jugada');
    
    // 2. Precios
    const preciosData = document.getElementById('precios-data');
    const PRECIOS = {
        quini6: parseFloat(preciosData.dataset.quini6),
        lotoplus: parseFloat(preciosData.dataset.lotoplus),
        loto5: parseFloat(preciosData.dataset.loto5),
        poceada: parseFloat(preciosData.dataset.poceada)
    };

    // 3. Estado
    const state = {
        modalidad: 'quiniela',
        selecciones: {
            quini6: [],
            lotoplus: [],
            loto5: [],
            poceada: []
        },
        max: {
            quini6: 6,
            lotoplus: 6,
            loto5: 5,
            poceada: 8
        }
    };

    // 4. Tabs
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

            inputModalidad.value = target;
            state.modalidad = target;
            
            updateResumen();
            checkValidity();
        });
    });

    // 5. Grillas de números
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

    const toggleNumero = (mod, num, btn) => {
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
        
        updateGridUI(mod);
        updateResumen();
        checkValidity();
    };

    const updateGridUI = (mod) => {
        const max = state.max[mod];
        const count = state.selecciones[mod].length;
        const container = document.getElementById(`grid-${mod}`);
        const buttons = container.querySelectorAll('.numero-btn');
        
        buttons.forEach(btn => {
            if (!btn.classList.contains('selected') && count >= max) {
                btn.disabled = true;
            } else {
                btn.disabled = false;
            }
        });
    };

    renderGrid('quini6', 0, 45, true);
    renderGrid('lotoplus', 0, 45, true);
    renderGrid('loto5', 0, 36, true);
    renderGrid('poceada', 0, 99, true);

    // 6. Validación y Resumen
    const inputQuinielaNum = document.getElementById('numero');
    const inputQuinielaPos = document.getElementById('posicion');
    const inputQuinielaJur = document.getElementById('jurisdiccion');
    const inputQuinielaImp = document.getElementById('importe');
    const inputLotoPlusPlus = document.getElementById('numero_plus');

    const updateResumen = () => {
        const mod = state.modalidad;
        let costo = 0;
        let status = '';

        if (mod === 'quiniela') {
            costo = parseFloat(inputQuinielaImp.value) || 0;
            status = 'Configurá tu jugada de Quiniela.';
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

        resumenMonto.textContent = costo.toLocaleString('es-AR', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
        resumenSeleccion.textContent = status;
    };

    const checkValidity = () => {
        const mod = state.modalidad;
        let valid = false;

        if (mod === 'quiniela') {
            const num = inputQuinielaNum.value;
            const pos = inputQuinielaPos.value;
            const jur = inputQuinielaJur.value;
            const imp = parseFloat(inputQuinielaImp.value) || 0;
            
            // Regex from backend
            const numRegex = /^\d{1,4}$/;
            
            if (numRegex.test(num) && pos && jur && imp > 0) {
                valid = true;
            }
        } else {
            const count = state.selecciones[mod].length;
            const max = state.max[mod];
            
            if (count === max) {
                if (mod === 'lotoplus') {
                    if (inputLotoPlusPlus.value !== '' && parseInt(inputLotoPlusPlus.value) >= 0 && parseInt(inputLotoPlusPlus.value) <= 9) {
                        valid = true;
                    }
                } else {
                    valid = true;
                }
            }
        }

        btnSubmit.disabled = !valid;
    };

    [inputQuinielaNum, inputQuinielaPos, inputQuinielaJur, inputQuinielaImp, inputLotoPlusPlus].forEach(el => {
        if (el) {
            el.addEventListener('input', () => {
                updateResumen();
                checkValidity();
            });
            el.addEventListener('change', () => {
                updateResumen();
                checkValidity();
            });
        }
    });

    // 7. Generación de arrays ocultos en submit
    formJugada.addEventListener('submit', (e) => {
        const mod = state.modalidad;
        
        // Remove existing dynamic inputs
        formJugada.querySelectorAll('.dynamic-array-input').forEach(el => el.remove());

        if (mod !== 'quiniela') {
            const nums = state.selecciones[mod];
            nums.forEach(num => {
                const input = document.createElement('input');
                input.type = 'hidden';
                input.name = `numeros_${mod}[]`;
                input.value = num;
                input.className = 'dynamic-array-input';
                formJugada.appendChild(input);
            });
        }
        
        // Prevent double submit natively just in case
        setTimeout(() => {
            btnSubmit.disabled = true;
        }, 10);
    });

    // INIT
    updateResumen();
    checkValidity();
});
