<div class="space-y-6">
    <div>
        <h1 class="font-display text-3xl tracking-wide text-wc-text">ACADEMIA</h1>
        <p class="mt-1 text-sm text-wc-text-secondary">6 calculadoras cientificas para optimizar tu entrenamiento y nutricion.</p>
    </div>

    {{-- Calculator Tabs --}}
    <div x-data="{ tab: 'tdee' }">
        <div class="flex flex-wrap gap-2">
            @foreach(['tdee' => 'TDEE', 'macros' => 'Macros', '1rm' => '1RM', 'imc' => 'IMC', 'hidratacion' => 'Hidratacion', 'volumen' => 'Volumen'] as $key => $label)
                <button x-on:click="tab = '{{ $key }}'" :class="tab === '{{ $key }}' ? 'bg-wc-accent text-white' : 'bg-wc-bg-tertiary text-wc-text-secondary hover:text-wc-text'" class="rounded-lg border border-wc-border px-4 py-2 text-sm font-medium transition-colors">{{ $label }}</button>
            @endforeach
        </div>

        {{-- TDEE Calculator --}}
        <div x-show="tab === 'tdee'" x-data="tdeeCalc()" class="mt-6 rounded-xl border border-wc-border bg-wc-bg-tertiary p-6">
            <h2 class="font-display text-xl tracking-wide text-wc-text">CALCULADORA TDEE</h2>
            <p class="mt-1 text-xs text-wc-text-tertiary">Formula Mifflin-St Jeor — la mas precisa para estimar gasto calorico diario.</p>
            <div class="mt-6 grid grid-cols-2 gap-4 sm:grid-cols-3">
                <div>
                    <label class="block text-xs font-medium text-wc-text-tertiary">Peso (kg)</label>
                    <input type="number" x-model.number="peso" step="0.1" class="mt-1 w-full rounded-lg border border-wc-border bg-wc-bg px-3 py-2 text-sm text-wc-text focus:border-wc-accent focus:outline-none" placeholder="70">
                </div>
                <div>
                    <label class="block text-xs font-medium text-wc-text-tertiary">Estatura (cm)</label>
                    <input type="number" x-model.number="estatura" class="mt-1 w-full rounded-lg border border-wc-border bg-wc-bg px-3 py-2 text-sm text-wc-text focus:border-wc-accent focus:outline-none" placeholder="170">
                </div>
                <div>
                    <label class="block text-xs font-medium text-wc-text-tertiary">Edad</label>
                    <input type="number" x-model.number="edad" class="mt-1 w-full rounded-lg border border-wc-border bg-wc-bg px-3 py-2 text-sm text-wc-text focus:border-wc-accent focus:outline-none" placeholder="25">
                </div>
                <div>
                    <label class="block text-xs font-medium text-wc-text-tertiary">Genero</label>
                    <select x-model="genero" class="mt-1 w-full rounded-lg border border-wc-border bg-wc-bg px-3 py-2 text-sm text-wc-text focus:border-wc-accent focus:outline-none">
                        <option value="m">Masculino</option>
                        <option value="f">Femenino</option>
                    </select>
                </div>
                <div class="col-span-2">
                    <label class="block text-xs font-medium text-wc-text-tertiary">Nivel de actividad</label>
                    <select x-model.number="actividad" class="mt-1 w-full rounded-lg border border-wc-border bg-wc-bg px-3 py-2 text-sm text-wc-text focus:border-wc-accent focus:outline-none">
                        <option value="1.2">Sedentario (escritorio, sin ejercicio)</option>
                        <option value="1.375">Ligero (1-3 dias/semana)</option>
                        <option value="1.55">Moderado (3-5 dias/semana)</option>
                        <option value="1.725">Activo (6-7 dias/semana)</option>
                        <option value="1.9">Muy activo (doble sesion)</option>
                    </select>
                </div>
            </div>
            <button x-on:click="calcular()" class="mt-4 rounded-lg bg-wc-accent px-6 py-2 text-sm font-semibold text-white hover:bg-wc-accent-hover">Calcular TDEE</button>
            <template x-if="resultado > 0">
                <div class="mt-6 grid grid-cols-2 gap-4 sm:grid-cols-4">
                    <div class="rounded-lg border border-wc-border bg-wc-bg p-4 text-center">
                        <p class="text-xs text-wc-text-tertiary">BMR</p>
                        <p class="mt-1 font-data text-xl font-bold text-wc-text" x-text="bmr + ' kcal'"></p>
                    </div>
                    <div class="rounded-lg border border-wc-accent/30 bg-wc-accent/5 p-4 text-center">
                        <p class="text-xs text-wc-text-tertiary">TDEE</p>
                        <p class="mt-1 font-data text-xl font-bold text-wc-accent" x-text="resultado + ' kcal'"></p>
                    </div>
                    <div class="rounded-lg border border-wc-border bg-wc-bg p-4 text-center">
                        <p class="text-xs text-wc-text-tertiary">Deficit (-400)</p>
                        <p class="mt-1 font-data text-xl font-bold text-wc-text" x-text="(resultado - 400) + ' kcal'"></p>
                    </div>
                    <div class="rounded-lg border border-wc-border bg-wc-bg p-4 text-center">
                        <p class="text-xs text-wc-text-tertiary">Superavit (+300)</p>
                        <p class="mt-1 font-data text-xl font-bold text-wc-text" x-text="(resultado + 300) + ' kcal'"></p>
                    </div>
                </div>
            </template>
        </div>

        {{-- Macros Calculator --}}
        <div x-show="tab === 'macros'" x-data="macrosCalc()" class="mt-6 rounded-xl border border-wc-border bg-wc-bg-tertiary p-6">
            <h2 class="font-display text-xl tracking-wide text-wc-text">CALCULADORA DE MACROS</h2>
            <p class="mt-1 text-xs text-wc-text-tertiary">Calcula proteina, carbohidratos y grasas segun tu TDEE y objetivo.</p>
            <div class="mt-6 grid grid-cols-2 gap-4 sm:grid-cols-3">
                <div>
                    <label class="block text-xs font-medium text-wc-text-tertiary">Calorias objetivo</label>
                    <input type="number" x-model.number="calorias" class="mt-1 w-full rounded-lg border border-wc-border bg-wc-bg px-3 py-2 text-sm text-wc-text focus:border-wc-accent focus:outline-none" placeholder="2200">
                </div>
                <div>
                    <label class="block text-xs font-medium text-wc-text-tertiary">Peso corporal (kg)</label>
                    <input type="number" x-model.number="peso" step="0.1" class="mt-1 w-full rounded-lg border border-wc-border bg-wc-bg px-3 py-2 text-sm text-wc-text focus:border-wc-accent focus:outline-none" placeholder="70">
                </div>
                <div>
                    <label class="block text-xs font-medium text-wc-text-tertiary">Proteina (g/kg)</label>
                    <select x-model.number="protRatio" class="mt-1 w-full rounded-lg border border-wc-border bg-wc-bg px-3 py-2 text-sm text-wc-text focus:border-wc-accent focus:outline-none">
                        <option value="1.6">1.6 g/kg (mantenimiento)</option>
                        <option value="2.0">2.0 g/kg (recomposicion)</option>
                        <option value="2.2">2.2 g/kg (deficit)</option>
                    </select>
                </div>
            </div>
            <button x-on:click="calcular()" class="mt-4 rounded-lg bg-wc-accent px-6 py-2 text-sm font-semibold text-white hover:bg-wc-accent-hover">Calcular Macros</button>
            <template x-if="proteina > 0">
                <div class="mt-6 grid grid-cols-3 gap-4">
                    <div class="rounded-lg border border-red-500/30 bg-red-500/5 p-4 text-center">
                        <p class="text-xs text-wc-text-tertiary">Proteina</p>
                        <p class="mt-1 font-data text-2xl font-bold text-red-500" x-text="proteina + 'g'"></p>
                        <p class="text-xs text-wc-text-tertiary" x-text="protKcal + ' kcal'"></p>
                    </div>
                    <div class="rounded-lg border border-yellow-500/30 bg-yellow-500/5 p-4 text-center">
                        <p class="text-xs text-wc-text-tertiary">Carbohidratos</p>
                        <p class="mt-1 font-data text-2xl font-bold text-yellow-500" x-text="carbs + 'g'"></p>
                        <p class="text-xs text-wc-text-tertiary" x-text="carbsKcal + ' kcal'"></p>
                    </div>
                    <div class="rounded-lg border border-indigo-500/30 bg-indigo-500/5 p-4 text-center">
                        <p class="text-xs text-wc-text-tertiary">Grasas</p>
                        <p class="mt-1 font-data text-2xl font-bold text-indigo-400" x-text="grasas + 'g'"></p>
                        <p class="text-xs text-wc-text-tertiary" x-text="grasasKcal + ' kcal'"></p>
                    </div>
                </div>
            </template>
        </div>

        {{-- 1RM Calculator --}}
        <div x-show="tab === '1rm'" x-data="rmCalc()" class="mt-6 rounded-xl border border-wc-border bg-wc-bg-tertiary p-6">
            <h2 class="font-display text-xl tracking-wide text-wc-text">CALCULADORA 1RM</h2>
            <p class="mt-1 text-xs text-wc-text-tertiary">Formula Brzycki — estima tu repeticion maxima sin arriesgar lesion.</p>
            <div class="mt-6 grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-medium text-wc-text-tertiary">Peso levantado (kg)</label>
                    <input type="number" x-model.number="pesoLevantado" step="0.5" class="mt-1 w-full rounded-lg border border-wc-border bg-wc-bg px-3 py-2 text-sm text-wc-text focus:border-wc-accent focus:outline-none" placeholder="80">
                </div>
                <div>
                    <label class="block text-xs font-medium text-wc-text-tertiary">Repeticiones realizadas</label>
                    <input type="number" x-model.number="reps" min="1" max="15" class="mt-1 w-full rounded-lg border border-wc-border bg-wc-bg px-3 py-2 text-sm text-wc-text focus:border-wc-accent focus:outline-none" placeholder="5">
                </div>
            </div>
            <button x-on:click="calcular()" class="mt-4 rounded-lg bg-wc-accent px-6 py-2 text-sm font-semibold text-white hover:bg-wc-accent-hover">Calcular 1RM</button>
            <template x-if="rm > 0">
                <div class="mt-6">
                    <div class="mb-4 rounded-lg border border-wc-accent/30 bg-wc-accent/5 p-4 text-center">
                        <p class="text-xs text-wc-text-tertiary">Tu 1RM estimado</p>
                        <p class="font-data text-3xl font-bold text-wc-accent" x-text="rm + ' kg'"></p>
                    </div>
                    <div class="grid grid-cols-3 gap-3 sm:grid-cols-5">
                        <template x-for="pct in [90, 85, 80, 75, 70]" :key="pct">
                            <div class="rounded-lg border border-wc-border bg-wc-bg p-3 text-center">
                                <p class="text-xs text-wc-text-tertiary" x-text="pct + '%'"></p>
                                <p class="mt-1 font-data text-lg font-bold text-wc-text" x-text="Math.round(rm * pct / 100) + ' kg'"></p>
                            </div>
                        </template>
                    </div>
                </div>
            </template>
        </div>

        {{-- IMC Calculator --}}
        <div x-show="tab === 'imc'" x-data="imcCalc()" class="mt-6 rounded-xl border border-wc-border bg-wc-bg-tertiary p-6">
            <h2 class="font-display text-xl tracking-wide text-wc-text">CALCULADORA IMC</h2>
            <p class="mt-1 text-xs text-wc-text-tertiary">Indice de Masa Corporal — referencia general, no indicador definitivo.</p>
            <div class="mt-6 grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-medium text-wc-text-tertiary">Peso (kg)</label>
                    <input type="number" x-model.number="peso" step="0.1" class="mt-1 w-full rounded-lg border border-wc-border bg-wc-bg px-3 py-2 text-sm text-wc-text focus:border-wc-accent focus:outline-none" placeholder="70">
                </div>
                <div>
                    <label class="block text-xs font-medium text-wc-text-tertiary">Estatura (cm)</label>
                    <input type="number" x-model.number="estatura" class="mt-1 w-full rounded-lg border border-wc-border bg-wc-bg px-3 py-2 text-sm text-wc-text focus:border-wc-accent focus:outline-none" placeholder="170">
                </div>
            </div>
            <button x-on:click="calcular()" class="mt-4 rounded-lg bg-wc-accent px-6 py-2 text-sm font-semibold text-white hover:bg-wc-accent-hover">Calcular IMC</button>
            <template x-if="imc > 0">
                <div class="mt-6 rounded-lg border border-wc-border bg-wc-bg p-4 text-center">
                    <p class="text-xs text-wc-text-tertiary">Tu IMC</p>
                    <p class="font-data text-3xl font-bold" :class="imc < 18.5 ? 'text-yellow-500' : imc < 25 ? 'text-green-500' : imc < 30 ? 'text-yellow-500' : 'text-red-500'" x-text="imc"></p>
                    <p class="mt-1 text-sm font-medium" :class="imc < 18.5 ? 'text-yellow-500' : imc < 25 ? 'text-green-500' : imc < 30 ? 'text-yellow-500' : 'text-red-500'" x-text="categoria"></p>
                </div>
            </template>
        </div>

        {{-- Hidratacion Calculator --}}
        <div x-show="tab === 'hidratacion'" x-data="hidratCalc()" class="mt-6 rounded-xl border border-wc-border bg-wc-bg-tertiary p-6">
            <h2 class="font-display text-xl tracking-wide text-wc-text">CALCULADORA DE HIDRATACION</h2>
            <p class="mt-1 text-xs text-wc-text-tertiary">Calcula tu ingesta diaria de agua recomendada.</p>
            <div class="mt-6 grid grid-cols-2 gap-4 sm:grid-cols-3">
                <div>
                    <label class="block text-xs font-medium text-wc-text-tertiary">Peso (kg)</label>
                    <input type="number" x-model.number="peso" step="0.1" class="mt-1 w-full rounded-lg border border-wc-border bg-wc-bg px-3 py-2 text-sm text-wc-text focus:border-wc-accent focus:outline-none" placeholder="70">
                </div>
                <div>
                    <label class="block text-xs font-medium text-wc-text-tertiary">Minutos ejercicio/dia</label>
                    <input type="number" x-model.number="ejercicio" class="mt-1 w-full rounded-lg border border-wc-border bg-wc-bg px-3 py-2 text-sm text-wc-text focus:border-wc-accent focus:outline-none" placeholder="60">
                </div>
                <div>
                    <label class="block text-xs font-medium text-wc-text-tertiary">Clima</label>
                    <select x-model.number="clima" class="mt-1 w-full rounded-lg border border-wc-border bg-wc-bg px-3 py-2 text-sm text-wc-text focus:border-wc-accent focus:outline-none">
                        <option value="0">Templado</option>
                        <option value="500">Caliente/humedo</option>
                    </select>
                </div>
            </div>
            <button x-on:click="calcular()" class="mt-4 rounded-lg bg-wc-accent px-6 py-2 text-sm font-semibold text-white hover:bg-wc-accent-hover">Calcular</button>
            <template x-if="litros > 0">
                <div class="mt-6 grid grid-cols-2 gap-4">
                    <div class="rounded-lg border border-blue-500/30 bg-blue-500/5 p-4 text-center">
                        <p class="text-xs text-wc-text-tertiary">Agua diaria</p>
                        <p class="font-data text-3xl font-bold text-blue-400" x-text="litros + ' L'"></p>
                    </div>
                    <div class="rounded-lg border border-wc-border bg-wc-bg p-4 text-center">
                        <p class="text-xs text-wc-text-tertiary">Equivale a</p>
                        <p class="font-data text-3xl font-bold text-wc-text" x-text="vasos + ' vasos'"></p>
                        <p class="text-xs text-wc-text-tertiary">(250ml c/u)</p>
                    </div>
                </div>
            </template>
        </div>

        {{-- Volumen Calculator --}}
        <div x-show="tab === 'volumen'" x-data="volumenCalc()" class="mt-6 rounded-xl border border-wc-border bg-wc-bg-tertiary p-6">
            <h2 class="font-display text-xl tracking-wide text-wc-text">CALCULADORA DE VOLUMEN</h2>
            <p class="mt-1 text-xs text-wc-text-tertiary">Volumen total de entrenamiento por grupo muscular (series x reps x peso).</p>
            <div class="mt-6 grid grid-cols-3 gap-4">
                <div>
                    <label class="block text-xs font-medium text-wc-text-tertiary">Series</label>
                    <input type="number" x-model.number="series" class="mt-1 w-full rounded-lg border border-wc-border bg-wc-bg px-3 py-2 text-sm text-wc-text focus:border-wc-accent focus:outline-none" placeholder="4">
                </div>
                <div>
                    <label class="block text-xs font-medium text-wc-text-tertiary">Reps por serie</label>
                    <input type="number" x-model.number="reps" class="mt-1 w-full rounded-lg border border-wc-border bg-wc-bg px-3 py-2 text-sm text-wc-text focus:border-wc-accent focus:outline-none" placeholder="10">
                </div>
                <div>
                    <label class="block text-xs font-medium text-wc-text-tertiary">Peso (kg)</label>
                    <input type="number" x-model.number="peso" step="0.5" class="mt-1 w-full rounded-lg border border-wc-border bg-wc-bg px-3 py-2 text-sm text-wc-text focus:border-wc-accent focus:outline-none" placeholder="60">
                </div>
            </div>
            <button x-on:click="calcular()" class="mt-4 rounded-lg bg-wc-accent px-6 py-2 text-sm font-semibold text-white hover:bg-wc-accent-hover">Calcular Volumen</button>
            <template x-if="volumen > 0">
                <div class="mt-6 grid grid-cols-3 gap-4">
                    <div class="rounded-lg border border-wc-accent/30 bg-wc-accent/5 p-4 text-center">
                        <p class="text-xs text-wc-text-tertiary">Volumen total</p>
                        <p class="font-data text-2xl font-bold text-wc-accent" x-text="volumen.toLocaleString() + ' kg'"></p>
                    </div>
                    <div class="rounded-lg border border-wc-border bg-wc-bg p-4 text-center">
                        <p class="text-xs text-wc-text-tertiary">Reps totales</p>
                        <p class="font-data text-2xl font-bold text-wc-text" x-text="totalReps"></p>
                    </div>
                    <div class="rounded-lg border border-wc-border bg-wc-bg p-4 text-center">
                        <p class="text-xs text-wc-text-tertiary">Tonelaje</p>
                        <p class="font-data text-2xl font-bold text-wc-text" x-text="(volumen/1000).toFixed(2) + ' ton'"></p>
                    </div>
                </div>
            </template>
        </div>
    </div>

    {{-- Alpine calculators --}}
    <script>
        function tdeeCalc() {
            return {
                peso: null, estatura: null, edad: null, genero: 'm', actividad: 1.55, bmr: 0, resultado: 0,
                calcular() {
                    if (!this.peso || !this.estatura || !this.edad) return;
                    this.bmr = Math.round(this.genero === 'm'
                        ? (10 * this.peso) + (6.25 * this.estatura) - (5 * this.edad) + 5
                        : (10 * this.peso) + (6.25 * this.estatura) - (5 * this.edad) - 161);
                    this.resultado = Math.round(this.bmr * this.actividad);
                }
            };
        }
        function macrosCalc() {
            return {
                calorias: null, peso: null, protRatio: 2.0, proteina: 0, carbs: 0, grasas: 0, protKcal: 0, carbsKcal: 0, grasasKcal: 0,
                calcular() {
                    if (!this.calorias || !this.peso) return;
                    this.proteina = Math.round(this.peso * this.protRatio);
                    this.protKcal = this.proteina * 4;
                    this.grasas = Math.round(this.peso * 0.9);
                    this.grasasKcal = this.grasas * 9;
                    this.carbsKcal = this.calorias - this.protKcal - this.grasasKcal;
                    this.carbs = Math.round(this.carbsKcal / 4);
                    if (this.carbs < 0) this.carbs = 0;
                    if (this.carbsKcal < 0) this.carbsKcal = 0;
                }
            };
        }
        function rmCalc() {
            return {
                pesoLevantado: null, reps: null, rm: 0,
                calcular() {
                    if (!this.pesoLevantado || !this.reps || this.reps < 1) return;
                    if (this.reps === 1) { this.rm = this.pesoLevantado; return; }
                    this.rm = Math.round(this.pesoLevantado / (1.0278 - 0.0278 * this.reps));
                }
            };
        }
        function imcCalc() {
            return {
                peso: null, estatura: null, imc: 0, categoria: '',
                calcular() {
                    if (!this.peso || !this.estatura) return;
                    const h = this.estatura / 100;
                    this.imc = (this.peso / (h * h)).toFixed(1);
                    this.categoria = this.imc < 18.5 ? 'Bajo peso' : this.imc < 25 ? 'Peso normal' : this.imc < 30 ? 'Sobrepeso' : 'Obesidad';
                }
            };
        }
        function hidratCalc() {
            return {
                peso: null, ejercicio: 0, clima: 0, litros: 0, vasos: 0,
                calcular() {
                    if (!this.peso) return;
                    const ml = (this.peso * 35) + (this.ejercicio * 12) + this.clima;
                    this.litros = (ml / 1000).toFixed(1);
                    this.vasos = Math.ceil(ml / 250);
                }
            };
        }
        function volumenCalc() {
            return {
                series: null, reps: null, peso: null, volumen: 0, totalReps: 0,
                calcular() {
                    if (!this.series || !this.reps || !this.peso) return;
                    this.totalReps = this.series * this.reps;
                    this.volumen = this.totalReps * this.peso;
                }
            };
        }
    </script>
</div>
