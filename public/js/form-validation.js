/**
 * MOOVA! — Validación Global de Formularios
 * Intercepta el submit de todos los formularios y verifica campos vacíos.
 * Usa SweetAlert2 para las alertas y animaciones CSS para resaltar campos.
 */
(function () {
    'use strict';

    // ── Estilos dinámicos para campos con error ──
    const style = document.createElement('style');
    style.textContent = `
        .moova-field-error {
            border-color: #ef4444 !important;
            box-shadow: 0 0 0 4px rgba(239, 68, 68, 0.12) !important;
            animation: moova-shake 0.4s ease-in-out;
        }
        .moova-field-error::placeholder {
            color: #f87171 !important;
        }
        .moova-error-label {
            color: #ef4444 !important;
            font-weight: 700;
        }
        @keyframes moova-shake {
            0%, 100% { transform: translateX(0); }
            20% { transform: translateX(-6px); }
            40% { transform: translateX(6px); }
            60% { transform: translateX(-4px); }
            80% { transform: translateX(4px); }
        }
    `;
    document.head.appendChild(style);

    /**
     * Limpia los estilos de error de un campo cuando el usuario empieza a escribir
     */
    function attachClearOnInput(field) {
        const handler = function () {
            field.classList.remove('moova-field-error');
            // Restaurar label si existe
            const wrapper = field.closest('.space-y-2') || field.closest('.space-y-3') || field.closest('div');
            if (wrapper) {
                const label = wrapper.querySelector('label');
                if (label) label.classList.remove('moova-error-label');
            }
            field.removeEventListener('input', handler);
            field.removeEventListener('change', handler);
        };
        field.addEventListener('input', handler);
        field.addEventListener('change', handler);
    }

    /**
     * Obtiene un nombre legible para el campo
     */
    function getFieldLabel(field) {
        // 1. Buscar el <label> asociado
        const wrapper = field.closest('.space-y-2') || field.closest('.space-y-3') || field.closest('div');
        if (wrapper) {
            const label = wrapper.querySelector('label');
            if (label) return label.textContent.trim().replace(/\*$/, '').trim();
        }

        // 2. Intentar con atributo placeholder
        if (field.placeholder) {
            return field.placeholder.replace(/^Ej\.\s*/i, '').trim();
        }

        // 3. Intentar con name
        if (field.name) {
            return field.name
                .replace(/_/g, ' ')
                .replace(/\b\w/g, l => l.toUpperCase());
        }

        return 'Este campo';
    }

    /**
     * Verifica si un campo está vacío o no tiene un valor válido
     */
    function isFieldEmpty(field) {
        const tag = field.tagName.toLowerCase();
        const type = (field.type || '').toLowerCase();

        // Ignorar campos ocultos y botones
        if (type === 'hidden' || type === 'submit' || type === 'button') return false;

        // Checkboxes: requeridos deben estar marcados
        if (type === 'checkbox') {
            return field.required && !field.checked;
        }

        // Radio buttons: se manejan aparte (verificar grupo)
        if (type === 'radio') return false;

        // Selects: verificar que se haya seleccionado algo con valor
        if (tag === 'select') {
            return field.required && (!field.value || field.value === '');
        }

        // Inputs de texto, email, password, number, textarea, etc.
        if (field.required) {
            const val = field.value.trim();
            return val === '';
        }

        return false;
    }

    /**
     * Valida grupos de radio buttons requeridos
     */
    function getEmptyRadioGroups(form) {
        const radioGroups = {};
        const radios = form.querySelectorAll('input[type="radio"][required]');
        
        radios.forEach(radio => {
            if (!radioGroups[radio.name]) {
                radioGroups[radio.name] = {
                    checked: false,
                    firstElement: radio,
                    label: getFieldLabel(radio)
                };
            }
            if (radio.checked) {
                radioGroups[radio.name].checked = true;
            }
        });

        return Object.values(radioGroups).filter(g => !g.checked);
    }

    /**
     * Función principal: interceptar el submit
     */
    document.addEventListener('DOMContentLoaded', function () {
        document.addEventListener('submit', function (e) {
            const form = e.target;
            if (!form || form.tagName.toLowerCase() !== 'form') return;

            const emptyFields = [];
            const fieldNames = [];

            // Verificar todos los campos del formulario
            const fields = form.querySelectorAll('input, select, textarea');
            fields.forEach(function (field) {
                // Limpiar errores previos
                field.classList.remove('moova-field-error');

                if (isFieldEmpty(field)) {
                    emptyFields.push(field);
                    fieldNames.push(getFieldLabel(field));
                }
            });

            // Verificar radio buttons
            const emptyRadios = getEmptyRadioGroups(form);
            emptyRadios.forEach(function (group) {
                emptyFields.push(group.firstElement);
                fieldNames.push(group.label || 'Selecciona una opción');
            });

            // Si hay campos vacíos, bloquear envío y mostrar alerta
            if (emptyFields.length > 0) {
                e.preventDefault();
                e.stopImmediatePropagation();

                // Resaltar campos vacíos
                emptyFields.forEach(function (field) {
                    field.classList.add('moova-field-error');
                    attachClearOnInput(field);

                    // Resaltar label
                    const wrapper = field.closest('.space-y-2') || field.closest('.space-y-3') || field.closest('div');
                    if (wrapper) {
                        const label = wrapper.querySelector('label');
                        if (label) label.classList.add('moova-error-label');
                    }
                });

                // Scroll al primer campo con error
                emptyFields[0].scrollIntoView({ behavior: 'smooth', block: 'center' });

                // Construir lista HTML de campos faltantes
                const uniqueNames = [...new Set(fieldNames)];
                const listHTML = uniqueNames
                    .map(name => `<div style="display:flex;align-items:center;gap:8px;padding:6px 0;"><i class="fas fa-circle-exclamation" style="color:#ef4444;font-size:14px;"></i><span style="text-align:left;color:#475569;font-weight:600;">${name}</span></div>`)
                    .join('');

                // Mostrar SweetAlert2
                if (typeof Swal !== 'undefined') {
                    Swal.fire({
                        icon: 'warning',
                        title: '¡Campos obligatorios!',
                        html: `
                            <p style="color:#64748b;margin-bottom:16px;font-size:14px;">
                                Por favor completa los siguientes campos antes de continuar:
                            </p>
                            <div style="background:#fef2f2;border:1px solid #fecaca;border-radius:16px;padding:16px 20px;text-align:left;max-height:200px;overflow-y:auto;">
                                ${listHTML}
                            </div>
                        `,
                        confirmButtonColor: '#0ea5e9',
                        confirmButtonText: '<i class="fas fa-pen mr-2"></i>Completar datos',
                        customClass: {
                            popup: 'rounded-[2rem]',
                            confirmButton: 'rounded-xl px-8 py-3 font-bold'
                        },
                        didClose: function () {
                            // Enfocar el primer campo con error
                            if (emptyFields[0] && typeof emptyFields[0].focus === 'function') {
                                emptyFields[0].focus();
                            }
                        }
                    });
                } else {
                    // Fallback si SweetAlert2 no está disponible
                    alert('Por favor completa todos los campos obligatorios:\n\n• ' + uniqueNames.join('\n• '));
                }

                return false;
            }
        }, true); // useCapture: true para interceptar antes de otros handlers
    });
})();
