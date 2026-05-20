    </main> <!-- Cierra main content area -->
    
    <!-- Footer Premium Tech-Dashboard -->
    <footer class="mt-auto bg-white/80 backdrop-blur-2xl border-t border-slate-200/60 relative z-10 px-4 sm:px-6 lg:px-10 py-4 sm:py-5">
        <div class="flex flex-col items-center gap-4 sm:gap-5 md:gap-6 md:flex-row md:justify-between">
            
            <!-- Izquierda: Copyright, Brand y Version -->
            <div class="flex items-center gap-3 sm:gap-3.5">
                <div class="w-8 h-8 sm:w-9 sm:h-9 rounded-[8px] sm:rounded-[10px] bg-gradient-to-br from-slate-800 to-slate-900 flex items-center justify-center text-white shadow-md border border-slate-700 shrink-0">
                    <i class="fas fa-bolt text-amber-400 text-[12px] sm:text-[14px]"></i>
                </div>
                <div>
                    <div class="flex items-baseline gap-2">
                        <span class="font-extrabold text-slate-800 outfit-font tracking-wide text-[12px] sm:text-[14px]">TRIGESTION</span>
                        <span class="text-[8px] sm:text-[9px] font-black text-indigo-600 uppercase tracking-widest border border-indigo-100 bg-indigo-50 px-1.5 py-0.5 rounded-md">v2.4.0</span>
                    </div>
                    <p class="text-[10px] sm:text-[11px] text-slate-500 font-medium mt-0.5 tracking-wide">
                        &copy; <?= date('Y') ?> MOOVA! Technologies. Todos los derechos reservados.
                    </p>
                </div>
            </div>

            <!-- Centro: Accesos Rápidos -->
            <div class="flex items-center gap-4 sm:gap-5 lg:gap-7 text-[11px] sm:text-[12px] font-bold text-slate-500">
                <a href="#" class="hover:text-indigo-600 transition-colors flex items-center gap-1.5"><i class="fas fa-book-open text-slate-400 text-[12px] sm:text-[13px]"></i> Docs</a>
                <a href="#" class="hover:text-indigo-600 transition-colors flex items-center gap-1.5"><i class="fas fa-shield-halved text-slate-400 text-[12px] sm:text-[13px]"></i> <span class="hidden xs:inline">Privacidad</span><span class="xs:hidden">Priv.</span></a>
                <a href="#" class="hover:text-indigo-600 transition-colors flex items-center gap-1.5"><i class="fas fa-headset text-slate-400 text-[12px] sm:text-[13px]"></i> <span class="hidden sm:inline">Soporte Técnico</span><span class="sm:hidden">Soporte</span></a>
            </div>

            <!-- Derecha: Indicadores Técnicos y Socials -->
            <div class="flex items-center gap-3 sm:gap-4 flex-wrap justify-center">
                <div class="flex items-center gap-2 sm:gap-3 bg-slate-50/80 px-2.5 sm:px-3 py-1 sm:py-1.5 rounded-lg border border-slate-200/80 shadow-sm">
                    <div class="flex items-center gap-1 sm:gap-1.5 border-r border-slate-200 pr-2 sm:pr-3" title="Estado de Base de Datos">
                        <i class="fas fa-server text-slate-400 text-[9px] sm:text-[10px]"></i>
                        <span class="text-[9px] sm:text-[10px] font-bold text-slate-600 uppercase tracking-wider">Conectado</span>
                    </div>
                    <div class="flex items-center gap-1 sm:gap-1.5" title="Estado General de la Nube">
                        <span class="relative flex h-2 w-2">
                            <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></span>
                            <span class="relative inline-flex rounded-full h-2 w-2 bg-emerald-500"></span>
                        </span>
                        <span class="text-[9px] sm:text-[10px] font-bold text-emerald-600 uppercase tracking-wider">Óptimo</span>
                    </div>
                </div>

                <div class="flex items-center gap-1.5 sm:gap-2">
                    <a href="#" class="w-7 h-7 sm:w-8 sm:h-8 rounded-lg bg-white border border-slate-200 flex items-center justify-center text-slate-400 hover:border-slate-400 hover:text-slate-700 transition-all shadow-sm"><i class="fab fa-github text-[12px] sm:text-[14px]"></i></a>
                    <a href="#" class="w-7 h-7 sm:w-8 sm:h-8 rounded-lg bg-white border border-slate-200 flex items-center justify-center text-slate-400 hover:border-sky-300 hover:text-sky-500 hover:bg-sky-50 transition-all shadow-sm"><i class="fab fa-twitter text-[12px] sm:text-[14px]"></i></a>
                </div>
            </div>
        </div>
    </footer>
</div> <!-- Cierra flex-1 flex flex-col min-h-screen (contenido principal) -->
</div> <!-- Cierra flex min-h-screen (layout global) -->

<!-- Validación global de formularios -->
<script src="../../public/js/form-validation.js"></script>
</body>
</html>