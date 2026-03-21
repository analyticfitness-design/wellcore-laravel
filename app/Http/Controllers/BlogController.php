<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class BlogController extends Controller
{
    private static array $articles = [
        [
            'slug' => 'progressive-overload-guia-completa',
            'title' => 'Sobrecarga Progresiva: La Clave del Crecimiento Muscular',
            'title_en' => 'Progressive Overload: The Foundation of Muscle Growth',
            'excerpt' => 'Descubre como aplicar correctamente el principio de sobrecarga progresiva para maximizar tus ganancias musculares y evitar el estancamiento.',
            'excerpt_en' => 'Learn how to correctly apply the principle of progressive overload to maximize muscle gains and break through training plateaus.',
            'category' => 'Entrenamiento',
            'category_en' => 'Training',
            'date' => '2025-08-12',
            'author' => 'WellCore Team',
            'reading_time' => '8 min',
            'gradient' => 'from-red-500/20 to-orange-500/10',
            'content_en' => '<h3>What Is Progressive Overload</h3>
<p>Progressive overload is the cornerstone principle of resistance training. First formalized by Thomas Delorme in the 1940s, it states that to drive continuous muscular adaptation, the training stimulus must be systematically increased over time. Without this progression, the body adapts to the existing demand and hypertrophy stalls — a phenomenon known as accommodation or the repeated bout effect.</p>

<p>Research published in the <em>Journal of Strength and Conditioning Research</em> consistently confirms that programs incorporating progressive overload produce significantly greater gains in both strength and hypertrophy compared to constant-load programs. The primary mechanism is mechanical tension: as muscles are subjected to progressively greater loads, signaling cascades including mTOR activation drive increased rates of muscle protein synthesis.</p>

<h3>Methods of Progression</h3>
<p>Progressive overload does not require adding weight to the bar every session — that approach works for novices but quickly reaches its ceiling. The main methods include: increasing load (most straightforward), adding total volume via additional sets or reps, improving training density by reducing rest periods, extending range of motion, and manipulating tempo to increase time under tension. The key is matching the progression method to your training age and current phase.</p>

<ul>
<li><strong>Load progression:</strong> 2.5–5 lb on compound lifts per week for beginners; 2.5 lb or less per week for intermediates</li>
<li><strong>Rep progression:</strong> Hold load constant and progress within a rep range (e.g., 8 to 12) before adding weight — the "double progression" model</li>
<li><strong>Volume progression:</strong> Add 1–2 sets per muscle group every 2–3 weeks, tracking weekly sets per muscle group</li>
<li><strong>Tempo manipulation:</strong> Extending the eccentric phase from 2 to 4 seconds increases mechanical tension without loading the joints further</li>
</ul>

<h3>Practical Progression Examples</h3>
<p>For a novice trainee on the bench press, a double-progression scheme might look like this: Week 1 — 135 lb x 3x8; Week 2 — 135 lb x 3x10; Week 3 — 140 lb x 3x8; Week 4 — 140 lb x 3x10. This approach is highly effective and sustainable because it separates rep targets from load increases, reducing the pressure of adding weight every session. For intermediate lifters, daily undulating periodization (DUP) — cycling heavy, moderate, and lighter days within the same week — allows continued progressive stimulus while managing fatigue accumulation.</p>

<h3>When to Program a Deload</h3>
<p>Linear progression is not indefinitely sustainable. Every 4–8 weeks — depending on accumulated training volume and intensity — a structured deload is warranted. During a deload week, either reduce volume by 40–50% while maintaining intensity, or reduce load by 10–15% while keeping volume similar. Clear signs you need a deload: stalled progress across all lifts for 2+ consecutive weeks, persistent joint discomfort (not just muscle soreness), disrupted sleep from systemic fatigue, and a noticeable drop in training motivation. A deload is not regression — it is the strategic investment that allows long-term progression to continue.</p>

<p>Remember that progressive overload plays out over months and years, not single sessions. The most successful athletes are those who advance consistently over the long term, not those who chase maximum load every workout and end up injured or burned out. Log your training, respect your recovery, and trust the process.</p>',
            'content' => '<h3>Que es la Sobrecarga Progresiva</h3>
<p>La sobrecarga progresiva es el principio fundamental del entrenamiento de fuerza. Establecido por Thomas Delorme en la decada de 1940, este principio dicta que para generar adaptaciones musculares continuas, el estimulo de entrenamiento debe incrementarse de manera sistematica con el tiempo. Sin esta progresion, el cuerpo se adapta al estimulo actual y el crecimiento muscular se detiene — un fenomeno conocido como acomodacion.</p>

<p>La investigacion publicada en el <em>Journal of Strength and Conditioning Research</em> confirma que los programas que incorporan sobrecarga progresiva producen ganancias significativamente mayores en fuerza e hipertrofia comparados con programas de carga constante. El mecanismo subyacente es la tension mecanica: cuando el musculo es sometido a cargas progresivamente mayores, se activan vias de senalizacion como mTOR que estimulan la sintesis proteica muscular.</p>

<h3>Metodos de Progresion</h3>
<p>Existen multiples formas de aplicar sobrecarga progresiva, y no todas requieren agregar mas peso a la barra. Los metodos principales incluyen: aumentar la carga (el mas directo), incrementar el volumen total mediante series o repeticiones adicionales, mejorar la densidad del entrenamiento reduciendo tiempos de descanso, aumentar el rango de movimiento, y manipular el tempo para incrementar el tiempo bajo tension. La clave es seleccionar el metodo apropiado segun tu nivel de experiencia y fase de entrenamiento.</p>

<ul>
<li><strong>Agregar peso:</strong> 1-2.5 kg en movimientos compuestos por semana (principiantes), 0.5-1 kg por semana (intermedios)</li>
<li><strong>Agregar repeticiones:</strong> Mantener el peso y progresar dentro del rango objetivo (ej: 8 a 12 reps) antes de subir carga</li>
<li><strong>Agregar series:</strong> Incrementar el volumen total de 1-2 series por grupo muscular cada 2-3 semanas</li>
<li><strong>Manipular tempo:</strong> Aumentar la fase excentrica de 2 a 4 segundos para mayor tension mecanica</li>
</ul>

<h3>Ejemplos Practicos de Progresion</h3>
<p>Para un principiante en press de banca: Semana 1 podria ser 60 kg x 3x8, Semana 2: 60 kg x 3x10, Semana 3: 62.5 kg x 3x8, Semana 4: 62.5 kg x 3x10. Este modelo de doble progresion (repeticiones primero, luego carga) es extremadamente efectivo y sostenible. Para intermedios, la progresion ondulante — alternando dias pesados, moderados y ligeros — permite manejar la fatiga mientras se mantiene el estimulo progresivo.</p>

<h3>Cuando Hacer un Deload</h3>
<p>La progresion no es lineal indefinidamente. Cada 4-8 semanas (dependiendo de la intensidad y volumen acumulados), es necesario implementar una semana de descarga o deload. Durante esta semana, reduce el volumen un 40-50% manteniendo la intensidad, o reduce la intensidad un 10-15% manteniendo el volumen. Los indicadores de que necesitas un deload incluyen: estancamiento en todas las progresiones durante 2+ semanas, dolor articular persistente, fatiga acumulada que afecta el sueno, y disminucion de la motivacion. El deload no es retroceder — es la inversion estrategica que permite seguir progresando a largo plazo.</p>

<p>Recuerda que la sobrecarga progresiva es un maraton, no un sprint. Los atletas mas exitosos son aquellos que progresan de manera consistente durante anos, no los que intentan agregar peso cada sesion y terminan lesionados. Documenta tus entrenamientos, respeta las senales de tu cuerpo, y confía en el proceso.</p>',
        ],
        [
            'slug' => 'periodizacion-entrenamiento',
            'title' => 'Periodizacion del Entrenamiento: Como Planificar tu Progreso',
            'title_en' => 'Training Periodization: How to Plan Your Progress',
            'excerpt' => 'Aprende a estructurar tus ciclos de entrenamiento con periodizacion lineal y ondulante para progresar de forma sostenible y evitar mesetas.',
            'excerpt_en' => 'Learn how to structure your training cycles using linear and undulating periodization to progress sustainably and break through plateaus.',
            'category' => 'Entrenamiento',
            'category_en' => 'Training',
            'date' => '2025-09-03',
            'author' => 'WellCore Team',
            'reading_time' => '10 min',
            'gradient' => 'from-blue-500/20 to-cyan-500/10',
            'content_en' => '<h3>Foundations of Periodization</h3>
<p>Periodization is the systematic organization of training into time blocks with specific goals. The concept was developed by Soviet scientist Lev Matveyev and later refined by Tudor Bompa, grounded in Hans Selye\'s General Adaptation Syndrome: the body responds to stress through alarm, resistance, and ultimately exhaustion. Periodization strategically manipulates these phases to maximize adaptation and minimize the risk of overtraining — the point at which stress exceeds the body\'s capacity to recover.</p>

<p>The classic framework divides training into macrocycles (the annual plan), mesocycles (3–6 week blocks), and microcycles (individual weeks). Each level has specific objectives that feed up to the next. Without this structure, training becomes random effort — and effort without direction rarely produces optimal results. This is not just a concern for competitive athletes; even recreational lifters respond significantly better to structured programming than to improvised training.</p>

<h3>Linear vs. Undulating Periodization</h3>
<p>Traditional linear periodization progresses from high volume/low intensity to low volume/high intensity across weeks or months. A classic example: Hypertrophy Phase (4 weeks, 4x10–12 at 65–75% 1RM) → Strength Phase (4 weeks, 4x5–6 at 80–85% 1RM) → Power Phase (3 weeks, 5x3 at 88–93% 1RM) → Deload (1 week). This model works well for beginners and early intermediates who benefit from focusing on one quality at a time.</p>

<ul>
<li><strong>Linear periodization:</strong> Ideal for beginners. Predictable progression, easy to program. Downside: untrained qualities can lose adaptations as the mesocycle progresses.</li>
<li><strong>Daily undulating periodization (DUP):</strong> Varies intensity and volume within the same week — e.g., Monday strength (5x5), Wednesday hypertrophy (3x10), Friday power (6x3). Meta-analyses show advantages for intermediate and advanced trainees over traditional linear models.</li>
<li><strong>Block periodization:</strong> Concentrates work on one primary quality per mesocycle while maintaining others with minimum effective volume. Commonly used by advanced athletes with defined competitive seasons.</li>
</ul>

<h3>Designing Your Mesocycles</h3>
<p>An effective mesocycle consists of 3–5 weeks of progressive loading followed by a deload week. Within each mesocycle, define clear variables: primary and accessory exercises, rep ranges, weekly load progression, and success metrics. For hypertrophy, a typical mesocycle progresses weekly volume per muscle group from roughly 10 to 20 sets, adding 2–4 sets per week. Maximum adaptive volume (MAV) varies by individual, but most trainees respond well in the 12–20 weekly sets per muscle group range. Exceeding maximum recoverable volume (MRV) consistently leads to overreaching.</p>

<h3>When to Change Programs</h3>
<p>One of the most common training mistakes is program-hopping — switching programs too frequently before adaptation has occurred. The evidence is clear: a program needs a minimum of 4–6 weeks to produce meaningful adaptation. Legitimate reasons to switch include completing the planned mesocycle, sustained stagnation (3+ weeks of zero progress on any variable), a significant shift in primary goals, or the need to accommodate an injury. Variety should come from within-program progression, not from changing the entire program every time it feels challenging. Embrace the discomfort — it is the signal that adaptation is occurring.</p>

<p>At WellCore, every training plan follows periodization principles adapted to your experience level, goals, and schedule. You do not need a sports science degree — you need an intelligent system that evolves with you.</p>',
            'content' => '<h3>Fundamentos de la Periodizacion</h3>
<p>La periodizacion es la organizacion sistematica del entrenamiento en bloques temporales con objetivos especificos. Este concepto, desarrollado inicialmente por el cientifico sovietico Lev Matveyev y refinado por Tudor Bompa, se basa en el Sindrome General de Adaptacion de Hans Selye: el cuerpo responde al estres con alarma, resistencia y eventualmente agotamiento. La periodizacion manipula estrategicamente estas fases para maximizar la adaptacion y minimizar el riesgo de sobreentrenamiento.</p>

<p>La estructura clasica divide el entrenamiento en macrociclos (plan anual), mesociclos (bloques de 3-6 semanas) y microciclos (semanas individuales). Cada nivel tiene objetivos especificos que alimentan al nivel superior. Sin esta estructura, el entrenamiento se convierte en esfuerzo aleatorio — y el esfuerzo sin direccion rara vez produce resultados optimos.</p>

<h3>Periodizacion Lineal vs Ondulante</h3>
<p>La periodizacion lineal tradicional progresa de alto volumen/baja intensidad a bajo volumen/alta intensidad a lo largo de semanas o meses. Por ejemplo: Fase de Hipertrofia (4 semanas, 4x10-12 al 65-75% 1RM), Fase de Fuerza (4 semanas, 4x5-6 al 80-85% 1RM), Fase de Potencia (3 semanas, 5x3 al 88-93% 1RM), Deload (1 semana). Este modelo funciona bien para principiantes e intermedios tempranos que se benefician de enfocarse en una cualidad a la vez.</p>

<ul>
<li><strong>Periodizacion lineal:</strong> Ideal para principiantes. Progresion predecible y facil de programar. Desventaja: las cualidades no entrenadas pueden perder adaptaciones.</li>
<li><strong>Periodizacion ondulante diaria (DUP):</strong> Varia la intensidad y volumen dentro de la misma semana. Ej: Lunes fuerza (5x5), Miercoles hipertrofia (3x10), Viernes potencia (6x3). Meta-analisis muestran ventajas para intermedios y avanzados.</li>
<li><strong>Periodizacion por bloques:</strong> Concentra el trabajo en una cualidad principal por mesociclo mientras mantiene las demas con volumen minimo. Popular en atletas avanzados con temporadas competitivas.</li>
</ul>

<h3>Disenando tus Mesociclos</h3>
<p>Un mesociclo efectivo tiene 3-5 semanas de carga progresiva seguidas de una semana de descarga. Dentro de cada mesociclo, define variables claras: ejercicios principales y accesorios, rangos de repeticiones, progresion de carga semanal, y metricas de exito. Para hipertrofia, un mesociclo tipico progresa el volumen semanal de 10 a 20 series por grupo muscular, incrementando 2-4 series por semana. El volumen maximo efectivo (MEV) varia individualmente, pero la mayoria responde bien entre 12-20 series semanales por grupo muscular.</p>

<h3>Cuando Cambiar de Programa</h3>
<p>Uno de los errores mas comunes es cambiar de programa demasiado frecuentemente — el sindrome del "program hopping". La evidencia sugiere que un programa necesita minimo 4-6 semanas para producir adaptaciones significativas. Los indicadores legitimos para cambiar incluyen: completar el mesociclo planificado, estancamiento sostenido (3+ semanas sin progresion en ninguna variable), cambio en objetivos primarios, o necesidad de manejar lesiones. La variedad debe venir de la progresion dentro del programa, no de cambiar el programa cada vez que se siente dificil.</p>

<p>En WellCore, cada plan de entrenamiento sigue principios de periodizacion adaptados a tu nivel, objetivos y disponibilidad. No necesitas ser un cientifico del deporte — necesitas un sistema inteligente que evolucione contigo.</p>',
        ],
        [
            'slug' => 'tdee-calcular-calorias',
            'title' => 'TDEE: Como Calcular tus Calorias Correctamente',
            'title_en' => 'TDEE: How to Calculate Your Calories Accurately',
            'excerpt' => 'Entiende tu gasto calorico total diario con formulas validadas cientificamente y aprende a ajustar tus calorias segun tus objetivos reales.',
            'excerpt_en' => 'Understand your total daily energy expenditure using scientifically validated equations and learn to dial in your calories for your actual goals.',
            'category' => 'Nutricion',
            'category_en' => 'Nutrition',
            'date' => '2025-10-18',
            'author' => 'WellCore Team',
            'reading_time' => '7 min',
            'gradient' => 'from-emerald-500/20 to-teal-500/10',
            'content_en' => '<h3>What Is TDEE</h3>
<p>TDEE — Total Daily Energy Expenditure — is the total number of calories your body burns in a day. It is composed of four components: basal metabolic rate (BMR, roughly 60–70% of TDEE), the thermic effect of food (TEF, 8–15%), exercise activity thermogenesis (EAT, 5–10%), and non-exercise activity thermogenesis (NEAT, 15–30%). Understanding and estimating your TDEE is the starting point for any body composition goal, whether that is fat loss, muscle gain, or maintenance. Without knowing your energy baseline, caloric targets are little more than guesswork.</p>

<p>It is important to recognize that TDEE is not a fixed number — it fluctuates daily based on activity level, sleep quality, stress, hormonal status, and even how much you fidget. Formulas provide an estimated starting point that must then be validated and adjusted using real bodyweight data over 2–3 weeks. Treat the formula output as a hypothesis, not a fact.</p>

<h3>Calculation Formulas</h3>
<p>The two most widely used and validated equations are the revised Harris-Benedict equation (1984) and the Mifflin-St Jeor equation (1990). Research published in the <em>Journal of the American Dietetic Association</em> found that Mifflin-St Jeor is the most accurate for the general population, with a mean error of approximately 10% — meaning it works well as a starting point but requires individual adjustment.</p>

<ul>
<li><strong>Mifflin-St Jeor (recommended):</strong> Men: (10 × weight in kg) + (6.25 × height in cm) − (5 × age) + 5. Women: (10 × weight in kg) + (6.25 × height in cm) − (5 × age) − 161</li>
<li><strong>Activity multipliers:</strong> Sedentary (×1.2), Lightly active (×1.375), Moderately active (×1.55), Very active (×1.725), Extremely active (×1.9)</li>
<li><strong>Practical example:</strong> A 176 lb male, 5\'10", age 30, training 4×/week: BMR ≈ 1,807 kcal. TDEE = 1,807 × 1.55 ≈ 2,800 kcal</li>
</ul>

<h3>Adjusting for Your Goal</h3>
<p>Once you have your estimated TDEE, apply the appropriate deficit or surplus. For fat loss, a 15–25% deficit is the evidence-backed sweet spot — aggressive enough to produce visible results, moderate enough to preserve muscle mass and maintain dietary adherence. For a 2,800 kcal TDEE, this means targeting roughly 2,100–2,380 kcal per day. For muscle gain, a 10–15% caloric surplus is sufficient to maximize the rate of muscle protein accretion — larger surpluses do not accelerate muscle growth meaningfully but do accelerate fat gain.</p>

<h3>Validation with Real Data</h3>
<p>Formulas are estimates. The only way to determine your actual TDEE is to track your food intake and bodyweight for 2–3 weeks. Weigh yourself daily under consistent conditions (morning, post-void, fasted), calculate the weekly average, and compare week over week. If your average weight holds steady, your average intake approximates your TDEE. If it drops 0.7–1 lb per week, you are in a deficit of roughly 350–500 kcal. Adjust in 100–200 kcal increments and reassess after two weeks. This data-driven approach eliminates the need to blindly trust any formula and is the same methodology used by precision nutrition coaches working with competitive athletes.</p>',
            'content' => '<h3>Que es el TDEE</h3>
<p>El TDEE (Total Daily Energy Expenditure) o Gasto Energetico Total Diario es la cantidad total de calorias que tu cuerpo quema en un dia. Compuesto por cuatro elementos principales: tu metabolismo basal (BMR, 60-70% del total), el efecto termico de los alimentos (TEF, 8-15%), la actividad fisica programada (EAT, 5-10%), y la termogenesis por actividad no programada (NEAT, 15-30%). Entender y calcular tu TDEE es el punto de partida para cualquier objetivo de composicion corporal, ya sea perder grasa, ganar musculo o mantener tu peso.</p>

<p>Es importante entender que el TDEE no es un numero fijo — fluctua diariamente segun tu actividad, estres, calidad de sueno, y estado hormonal. Las formulas nos dan un punto de partida estimado, que luego debemos validar y ajustar con datos reales de peso y medidas durante 2-3 semanas.</p>

<h3>Formulas de Calculo</h3>
<p>Las dos formulas mas utilizadas y validadas cientificamente son la ecuacion de Harris-Benedict (revisada en 1984) y la ecuacion de Mifflin-St Jeor (1990). La investigacion publicada en el <em>Journal of the American Dietetic Association</em> encontro que la ecuacion de Mifflin-St Jeor es la mas precisa para la poblacion general, con un margen de error del 10%.</p>

<ul>
<li><strong>Mifflin-St Jeor (recomendada):</strong> Hombres: (10 x peso kg) + (6.25 x altura cm) - (5 x edad) + 5. Mujeres: (10 x peso kg) + (6.25 x altura cm) - (5 x edad) - 161</li>
<li><strong>Multiplicadores de actividad:</strong> Sedentario (x1.2), Ligeramente activo (x1.375), Moderadamente activo (x1.55), Muy activo (x1.725), Extra activo (x1.9)</li>
<li><strong>Ejemplo practico:</strong> Hombre de 80 kg, 178 cm, 30 anos, entrena 4x/semana: BMR = 1,781 kcal. TDEE = 1,781 x 1.55 = 2,760 kcal</li>
</ul>

<h3>Ajustes Segun Objetivos</h3>
<p>Una vez calculado tu TDEE estimado, aplica el deficit o superavit segun tu objetivo. Para perdida de grasa, un deficit del 15-25% es optimo — lo suficientemente agresivo para ver resultados pero lo suficientemente moderado para preservar musculo y adherencia. Para un TDEE de 2,760 kcal, esto significa 2,070-2,345 kcal diarias. Para ganancia muscular, un superavit del 10-15% es suficiente — superavits mayores no aceleran la ganancia muscular pero si aumentan la acumulacion de grasa.</p>

<h3>Validacion con Datos Reales</h3>
<p>Las formulas son estimaciones. La unica forma de conocer tu TDEE real es rastrear tu ingesta y peso corporal durante 2-3 semanas. Pesate diariamente en ayunas, calcula el promedio semanal, y compara semana a semana. Si tu peso promedio se mantiene estable, tu ingesta promedio es tu TDEE real. Si baja 0.3-0.5 kg por semana, estas en un deficit de aproximadamente 350-500 kcal. Ajusta en incrementos de 100-200 kcal y reevalua cada 2 semanas. Este enfoque basado en datos elimina la necesidad de confiar ciegamente en las formulas.</p>',
        ],
        [
            'slug' => 'macros-recomposicion-corporal',
            'title' => 'Macronutrientes para Recomposicion Corporal',
            'title_en' => 'Macronutrients for Body Recomposition',
            'excerpt' => 'Aprende a distribuir proteinas, carbohidratos y grasas para ganar musculo y perder grasa simultaneamente con estrategias basadas en evidencia.',
            'excerpt_en' => 'Learn how to distribute protein, carbohydrates, and fat to simultaneously build muscle and lose fat using evidence-based strategies.',
            'category' => 'Nutricion',
            'category_en' => 'Nutrition',
            'date' => '2025-11-25',
            'author' => 'WellCore Team',
            'reading_time' => '9 min',
            'gradient' => 'from-violet-500/20 to-purple-500/10',
            'content_en' => '<h3>Is Body Recomposition Real</h3>
<p>Body recomposition — simultaneously gaining muscle while losing fat — was long dismissed as impossible in mainstream fitness circles. The prevailing wisdom held that you had to choose: bulk or cut. Current research tells a more nuanced story. Studies published in <em>Medicine & Science in Sports & Exercise</em> have documented successful recomposition in untrained beginners, individuals with higher body fat levels, returning athletes after a detraining period, and people optimizing their nutrition for the first time. The key is not just caloric balance but macronutrient distribution — specifically adequate protein in the context of a structured resistance training program.</p>

<p>Recomposition works because muscle protein synthesis (MPS) and lipolysis (fat oxidation) are not mutually exclusive physiological processes. Both can occur simultaneously when the training stimulus is sufficient and protein intake is optimized. The common mistake is fixating exclusively on total calories while neglecting macronutrient composition — which determines where those calories come from and how the body partitions them.</p>

<h3>Protein: The Non-Negotiable Macronutrient</h3>
<p>For body recomposition, protein intake is the single most important nutritional variable. The evidence is remarkably consistent: 0.7–1.0 g of protein per pound of bodyweight (1.6–2.2 g/kg) represents the optimal range. A 2018 systematic review by Morton et al. published in the <em>British Journal of Sports Medicine</em> — analyzing data from over 1,800 subjects — confirmed that intakes above 1.62 g/kg maximized MPS response to resistance training. Targeting 1.0 g/lb bodyweight provides a comfortable margin without unnecessarily complicating dietary tracking.</p>

<ul>
<li><strong>Protein:</strong> 0.7–1.0 g per lb of bodyweight. Distribute across 3–5 meals with at least 30–40 g per meal to maximize the MPS response per feeding</li>
<li><strong>Fat:</strong> Minimum 0.35–0.5 g/lb for optimal hormonal function (testosterone, estrogens, cortisol regulation). Do not drop below 20% of total calories</li>
<li><strong>Carbohydrates:</strong> Fill the remaining caloric budget. Prioritize them around training to fuel performance and support recovery. Most active individuals do well at 1.5–2.5 g/lb bodyweight</li>
</ul>

<h3>Calorie Cycling for Recomposition</h3>
<p>An advanced but effective strategy is calorie cycling: consuming more calories on training days (at or slightly above maintenance) and fewer on rest days (a moderate deficit). This approach exploits post-exercise insulin sensitivity and the 24–48 hour window of elevated muscle protein synthesis that follows resistance training. A common protocol: training days at TDEE +10% with higher carbohydrates, rest days at TDEE −20% with moderate fat intake. The net weekly caloric balance creates a slight deficit that favors fat loss without impairing the muscle-building signal on training days.</p>

<h3>Who Responds Best to Recomposition</h3>
<p>Not everyone recomposes at the same rate. Ideal candidates include: true beginners to resistance training (the first 6–12 months represent the highest rate of "newbie gains"), individuals carrying above-average body fat (≥18–20% for men, ≥25–28% for women), athletes returning after a detraining period (muscle memory accelerates regain), and anyone who has never systematically tracked protein. For advanced trainees with low body fat, dedicated bulk-and-cut cycles typically produce more efficient results than attempting recomposition. Recomposition requires patience — progress is best measured with progress photos, tape measurements, and strength metrics rather than the scale, which fluctuates with water retention.</p>

<p>At WellCore, macronutrient targets are individualized based on your body composition, training level, food preferences, and lifestyle. There is no universal formula — there is the right formula for you.</p>',
            'content' => '<h3>Es Posible la Recomposicion Corporal</h3>
<p>La recomposicion corporal — ganar musculo mientras se pierde grasa — fue considerada imposible durante anos por la comunidad fitness. Sin embargo, la investigacion actual demuestra que es no solo posible sino predecible bajo condiciones especificas. Estudios publicados en <em>Medicine & Science in Sports & Exercise</em> han documentado recomposicion exitosa en principiantes, personas con sobrepeso, atletas que retoman el entrenamiento despues de un descanso, y personas que optimizan su nutricion por primera vez. La clave esta en la distribucion correcta de macronutrientes.</p>

<p>La recomposicion funciona porque la sintesis proteica muscular y la oxidacion de grasas son procesos fisiologicos que pueden ocurrir simultaneamente, siempre que el estimulo de entrenamiento y la ingesta proteica sean adecuados. El error comun es enfocarse exclusivamente en las calorias totales ignorando la composicion de esas calorias.</p>

<h3>Proteina: El Macronutriente Rey</h3>
<p>Para recomposicion corporal, la ingesta proteica es la variable nutricional mas critica. La evidencia es consistente: 1.6-2.2 g de proteina por kilogramo de peso corporal es el rango optimo. Una revision sistematica de Morton et al. (2018) publicada en el <em>British Journal of Sports Medicine</em> confirmo que ingestas superiores a 1.6 g/kg maximizan la sintesis proteica muscular. Para la mayoria de las personas, apuntar a 2.0 g/kg ofrece un margen de seguridad sin complicar excesivamente la dieta.</p>

<ul>
<li><strong>Proteina:</strong> 1.6-2.2 g/kg de peso corporal. Distribuir en 3-5 comidas con minimo 25-40g por comida para maximizar la respuesta anabolica</li>
<li><strong>Grasas:</strong> Minimo 0.7-1.0 g/kg para funcion hormonal optima (testosterona, estrogenos). No reducir por debajo de 20% de calorias totales</li>
<li><strong>Carbohidratos:</strong> El remanente calorico. Priorizar alrededor del entrenamiento para rendimiento y recuperacion. 3-5 g/kg para la mayoria de personas activas</li>
</ul>

<h3>Ciclado de Calorias para Recomposicion</h3>
<p>Una estrategia avanzada pero efectiva es el ciclado calorico: consumir mas calorias los dias de entrenamiento (ligero superavit o mantenimiento) y menos los dias de descanso (deficit moderado). Esto aprovecha la ventana de sensibilidad a la insulina post-entrenamiento y los picos de sintesis proteica muscular (que duran 24-48 horas). Un protocolo comun: dias de entrenamiento a TDEE +10% con carbohidratos altos, dias de descanso a TDEE -20% con grasas moderadas. El resultado neto semanal es un deficit leve que favorece la perdida de grasa sin comprometer la ganancia muscular.</p>

<h3>Condiciones Optimas para Recomposicion</h3>
<p>No todos responden igual a la recomposicion. Los candidatos ideales incluyen: principiantes en el entrenamiento de fuerza (primeros 6-12 meses), personas con porcentaje de grasa corporal superior al 18-20% (hombres) o 25-28% (mujeres), atletas que regresan despues de un periodo de desentrenamiento, y personas que nunca han optimizado su nutricion. Para intermedios y avanzados con porcentajes de grasa bajos, fases dedicadas de volumen y definicion suelen ser mas eficientes. La recomposicion requiere paciencia — los cambios ocurren lentamente y se miden mejor con fotos de progreso, medidas y fuerza que con la bascula.</p>

<p>En WellCore, personalizamos la distribucion de macronutrientes segun tu composicion corporal, nivel de entrenamiento, preferencias alimentarias y estilo de vida. No existe una formula universal — existe la formula correcta para ti.</p>',
        ],
        [
            'slug' => 'cardio-vs-pesas-mejor',
            'title' => 'Cardio vs Pesas: Cual es Mejor para tus Objetivos?',
            'excerpt' => 'Analisis basado en evidencia sobre que tipo de ejercicio es mas efectivo para perder grasa, ganar musculo y mejorar tu salud general.',
            'category' => 'Entrenamiento',
            'date' => '2026-01-08',
            'author' => 'WellCore Team',
            'reading_time' => '6 min',
            'gradient' => 'from-amber-500/20 to-yellow-500/10',
            'content' => '<h3>El Debate Resuelto por la Ciencia</h3>
<p>La pregunta "cardio o pesas?" es una de las mas frecuentes en el fitness, y la respuesta basada en evidencia es clara: depende de tu objetivo, pero para la mayoria de las personas que buscan mejorar su composicion corporal, el entrenamiento de fuerza deberia ser la prioridad. Un meta-analisis de Westcott (2012) publicado en <em>Current Sports Medicine Reports</em> demostro que el entrenamiento de fuerza no solo aumenta la masa muscular sino que incrementa el metabolismo basal, mejora la sensibilidad a la insulina y reduce la grasa visceral de manera comparable al cardio.</p>

<p>Esto no significa que el cardio sea inutil — lejos de ello. El ejercicio cardiovascular tiene beneficios unicos para la salud cardiometabolica, la capacidad aerobica, y la gestion del estres que el entrenamiento de fuerza no puede replicar completamente. La estrategia optima integra ambas modalidades de manera inteligente.</p>

<h3>Para Perdida de Grasa</h3>
<p>El cardio quema mas calorias por sesion, pero el entrenamiento de fuerza tiene un efecto protector sobre la masa muscular durante un deficit calorico. Estudios de Kraemer et al. muestran que personas en deficit calorico que solo hacen cardio pierden significativamente mas masa muscular que aquellas que combinan cardio con pesas. Perder musculo reduce tu metabolismo basal, creando el efecto "rebote" tan comun en dietas agresivas. La combinacion ideal para perdida de grasa: 3-4 sesiones de fuerza + 2-3 sesiones de cardio moderado (o incrementar el NEAT diario).</p>

<ul>
<li><strong>NEAT (Non-Exercise Activity Thermogenesis):</strong> Caminar, subir escaleras, moverse durante el dia. Puede representar 300-800 kcal diarias y es mas sostenible que sesiones largas de cardio</li>
<li><strong>Entrenamiento concurrente:</strong> Combinar fuerza y cardio en la misma semana. Investigacion sugiere separar las sesiones por minimo 6 horas o hacerlas en dias diferentes para minimizar la interferencia</li>
<li><strong>HIIT vs LISS:</strong> El HIIT es mas eficiente en tiempo pero genera mas fatiga. El LISS (caminar, bici suave) es menos estresante y mas facil de recuperar. Para la mayoria, una combinacion de ambos es optima</li>
</ul>

<h3>Recomendaciones por Objetivo</h3>
<p>Si tu objetivo es ganar musculo, prioriza el entrenamiento de fuerza (4-5 sesiones) y limita el cardio a 2-3 sesiones de baja intensidad para no interferir con la recuperacion. Si tu objetivo es perder grasa, mantén 3-4 sesiones de fuerza como base no negociable y usa el cardio como herramienta complementaria. Si tu objetivo es salud general, 3 sesiones de fuerza + 150 minutos de actividad cardiovascular moderada por semana cumple con las guias de la OMS y produce beneficios significativos en longevidad y calidad de vida.</p>

<p>En WellCore, nunca presentamos cardio y pesas como opciones opuestas. Tu plan integra ambas modalidades de forma personalizada, priorizando segun tus objetivos actuales y ajustando en cada fase de tu progresion.</p>',
        ],
        [
            'slug' => 'sueno-ganancia-muscular',
            'title' => 'Sueno y Ganancia Muscular: La Variable que Ignoras',
            'excerpt' => 'La ciencia demuestra que el sueno es tan importante como el entrenamiento y la nutricion para el crecimiento muscular. Descubre por que y como optimizarlo.',
            'category' => 'Recuperacion',
            'date' => '2026-01-22',
            'author' => 'WellCore Team',
            'reading_time' => '7 min',
            'gradient' => 'from-sky-500/20 to-indigo-500/10',
            'content' => '<h3>El Sueno Como Anabolico Natural</h3>
<p>Mientras duermes, tu cuerpo ejecuta procesos criticos para la ganancia muscular que no ocurren (o ocurren de manera suboptima) durante la vigilia. La hormona de crecimiento (GH), uno de los principales mediadores de la reparacion y crecimiento muscular, alcanza su pico de secrecion durante las fases de sueno profundo (ondas lentas), especialmente en el primer ciclo de sueno. Estudios de Van Cauter et al. publicados en <em>JAMA</em> demostraron que la restriccion de sueno a 4-5 horas reduce la secrecion de GH hasta un 70%.</p>

<p>Ademas de la GH, el sueno regula la testosterona, el cortisol, la sensibilidad a la insulina y la sintesis proteica muscular. Una sola semana de restriccion de sueno (5 horas por noche) puede reducir los niveles de testosterona en hombres jovenes sanos entre un 10-15%, equivalente a envejecer 10-15 anos en terminos hormonales. No existe suplemento, protocolo de entrenamiento o dieta que compense un sueno cronicamente insuficiente.</p>

<h3>Impacto en el Rendimiento</h3>
<p>La falta de sueno no solo afecta la recuperacion — deteriora directamente el rendimiento en el gimnasio. Investigacion de Reilly y Piercy mostro que la privacion de sueno reduce la fuerza maxima, la potencia, y la resistencia muscular. Tambien afecta la coordinacion, el tiempo de reaccion y la percepcion del esfuerzo (RPE), lo que significa que el mismo peso se siente mas pesado cuando estas mal descansado. En terminos practicos, dormir mal una noche puede reducir tu rendimiento un 5-10%, y la privacion cronica amplifica estos efectos de manera acumulativa.</p>

<ul>
<li><strong>Recomendacion de duracion:</strong> 7-9 horas por noche para adultos activos. Los atletas pueden beneficiarse de 8-10 horas durante fases de entrenamiento intenso</li>
<li><strong>Calidad sobre cantidad:</strong> 7 horas de sueno profundo e ininterrumpido son superiores a 9 horas de sueno fragmentado. La eficiencia del sueno (tiempo dormido / tiempo en cama) deberia ser superior al 85%</li>
<li><strong>Consistencia:</strong> Mantener horarios regulares de sueno (incluso fines de semana) mejora la calidad del sueno y la regulacion circadiana mas que cualquier otro factor</li>
</ul>

<h3>Higiene del Sueno Practica</h3>
<p>Optimizar tu sueno no requiere equipos costosos ni suplementos exoticos. Las estrategias con mayor evidencia incluyen: mantener la habitacion fresca (18-20 grados C), oscura y silenciosa; evitar pantallas 30-60 minutos antes de dormir (la luz azul suprime la melatonina); establecer una rutina pre-sueno consistente; limitar la cafeina despues de las 14:00 (la vida media de la cafeina es 5-7 horas); y evitar comidas pesadas 2-3 horas antes de acostarte, aunque un snack ligero con caseina puede favorecer la sintesis proteica nocturna.</p>

<p>Si entrenas seriamente y duermes menos de 7 horas consistentemente, mejorar tu sueno probablemente te dara mas resultados que cambiar tu programa de entrenamiento o ajustar tus macros. Es la variable mas ignorada y potencialmente la mas impactante en tu progreso.</p>',
        ],
        [
            'slug' => 'mindset-fitness-disciplina',
            'title' => 'Mindset Fitness: De la Motivacion a la Disciplina',
            'excerpt' => 'La motivacion te empieza, la disciplina te mantiene. Aprende a construir habitos sostenibles y superar los inevitables momentos de estancamiento.',
            'category' => 'Mindset',
            'date' => '2026-02-05',
            'author' => 'WellCore Team',
            'reading_time' => '6 min',
            'gradient' => 'from-rose-500/20 to-pink-500/10',
            'content' => '<h3>El Problema de Depender de la Motivacion</h3>
<p>La motivacion es una emocion, y como toda emocion, es transitoria. La psicologia del comportamiento ha demostrado consistentemente que la motivacion fluctua segun factores como el sueno, el estres, el estado de animo, y hasta la hora del dia. Construir un fisico o mejorar tu salud requiere consistencia durante meses y anos — un periodo en el que la motivacion inevitablemente desaparecera multiples veces. Estudios de Duckworth et al. sobre el "grit" (perseverancia) muestran que la consistencia a largo plazo es un predictor mas fuerte de exito que el talento o la motivacion inicial.</p>

<p>Esto no significa que la motivacion sea inutil. Es el combustible inicial que te impulsa a empezar, a inscribirte, a dar el primer paso. Pero necesitas un sistema que funcione independientemente de tu estado emocional. Ese sistema se construye con habitos, identidad y estructura.</p>

<h3>Construccion de Habitos Basada en Evidencia</h3>
<p>James Clear, basandose en decadas de investigacion en psicologia conductual, identifica cuatro leyes para construir habitos efectivos: hacerlo obvio (senales ambientales), hacerlo atractivo (recompensas inmediatas), hacerlo facil (reducir friccion), y hacerlo satisfactorio (seguimiento visible). Aplicado al fitness: prepara tu ropa de entrenamiento la noche anterior (obvio), entrena con musica o podcasts que disfrutes (atractivo), empieza con sesiones de 30 minutos en lugar de 90 (facil), y registra cada entrenamiento completado (satisfactorio).</p>

<ul>
<li><strong>Regla de los 2 minutos:</strong> Si un habito parece abrumador, reducelo a 2 minutos. "Ir al gimnasio" se convierte en "ponerme los tenis de entrenamiento". La accion minima genera inercia</li>
<li><strong>Apilamiento de habitos:</strong> Conecta el nuevo habito con uno existente. "Despues de mi cafe de la manana, preparo mi bolsa del gym"</li>
<li><strong>Enfoque basado en identidad:</strong> En lugar de "quiero perder peso", adopta "soy una persona que entrena". Los habitos que refuerzan tu identidad se mantienen mas facilmente</li>
</ul>

<h3>Navegando los Estancamientos</h3>
<p>Los plateaus son inevitables y, paradojicamente, son senales de que tu cuerpo se ha adaptado — lo cual es exactamente lo que el entrenamiento busca lograr. El problema no es el estancamiento en si, sino la respuesta emocional que genera. La mayoria de las personas abandonan justo cuando estan a punto de romper la meseta. Estrategias practicas: cambia una variable a la vez (no todo el programa), revisa tu sueno y nutricion antes de culpar al entrenamiento, busca PRs en diferentes metricas (no solo peso en barra), y recuerda que el progreso no lineal es la norma, no la excepcion.</p>

<p>En WellCore, tu coach no solo programa tu entrenamiento — te ayuda a construir el framework mental para la consistencia a largo plazo. Porque el mejor programa del mundo es inutil si no lo ejecutas semana tras semana.</p>',
        ],
        [
            'slug' => 'hidratacion-rendimiento-deportivo',
            'title' => 'Hidratacion y Rendimiento: Cuanto Agua Realmente Necesitas',
            'excerpt' => 'Desmitificamos los "8 vasos diarios" y te damos pautas reales basadas en ciencia para optimizar tu hidratacion segun tu actividad y composicion corporal.',
            'category' => 'Nutricion',
            'date' => '2026-02-20',
            'author' => 'WellCore Team',
            'reading_time' => '5 min',
            'gradient' => 'from-cyan-500/20 to-blue-500/10',
            'content' => '<h3>Mas Alla de los 8 Vasos</h3>
<p>La recomendacion de "8 vasos de agua al dia" no tiene base cientifica solida. El requerimiento hidrico real depende de tu peso corporal, nivel de actividad, temperatura ambiental, composicion de la dieta, y tasa de sudoracion individual. El Instituto de Medicina recomienda aproximadamente 3.7 litros diarios para hombres y 2.7 litros para mujeres de ingesta total de agua (incluyendo la proveniente de alimentos, que representa un 20-30% del total). Para personas fisicamente activas, estos valores aumentan significativamente.</p>

<p>Una formula practica y personalizada es consumir 35-40 ml de agua por kilogramo de peso corporal como base, y agregar 500-750 ml por cada hora de ejercicio. Para una persona de 80 kg, esto significa 2.8-3.2 litros como base diaria, mas el reemplazo de perdidas por sudor durante el entrenamiento.</p>

<h3>Deshidratacion y Rendimiento</h3>
<p>Los efectos de la deshidratacion en el rendimiento fisico estan bien documentados. Investigacion publicada en el <em>Journal of Athletic Training</em> demuestra que una perdida de tan solo el 2% del peso corporal en agua reduce la fuerza maxima un 6%, la potencia un 12%, y la resistencia muscular hasta un 20%. Ademas, la deshidratacion deteriora la funcion cognitiva, la toma de decisiones y la tolerancia al calor. Muchas personas comienzan su entrenamiento ya deshidratadas sin saberlo, comprometiendo su rendimiento desde la primera serie.</p>

<ul>
<li><strong>Indicadores de hidratacion:</strong> El color de la orina es el indicador mas practico. Amarillo palido = bien hidratado. Amarillo oscuro = necesitas beber mas. Transparente = posible sobrehidratacion</li>
<li><strong>Pre-entrenamiento:</strong> 400-600 ml de agua 2-3 horas antes del ejercicio. 200-300 ml 15-20 minutos antes</li>
<li><strong>Durante el entrenamiento:</strong> 150-250 ml cada 15-20 minutos, especialmente en sesiones superiores a 60 minutos</li>
<li><strong>Post-entrenamiento:</strong> Reemplazar el 150% del peso perdido durante el ejercicio. Si perdiste 0.5 kg, bebe 750 ml en las 2-4 horas siguientes</li>
</ul>

<h3>Electrolitos: Cuando Son Necesarios</h3>
<p>Para entrenamientos menores a 60 minutos en condiciones normales, el agua sola es suficiente. Sin embargo, durante sesiones prolongadas (>60 min), entrenamiento en calor, o personas con alta tasa de sudoracion, la reposicion de electrolitos — especialmente sodio — se vuelve importante. La hiponatremia (niveles bajos de sodio) por sobrehidratacion con agua sola puede ser tan peligrosa como la deshidratacion. Una solucion practica es agregar una pizca de sal (1/4 cucharadita) a tu botella de agua durante entrenamientos intensos, o usar bebidas electroliticas sin azucares anadidos.</p>

<p>La hidratacion es una de esas variables "invisibles" que afectan todo: tu fuerza, tu concentracion, tu digestion, y tu recuperacion. No necesitas obsesionarte, pero si necesitas un sistema minimo que garantice una ingesta adecuada a lo largo del dia.</p>',
        ],
        [
            'slug' => 'periodizacion-mujeres-ciclo',
            'title' => 'Entrenamiento y Ciclo Menstrual: Guia Basada en Evidencia',
            'excerpt' => 'Como adaptar tu entrenamiento y nutricion a las fases del ciclo menstrual para optimizar rendimiento, recuperacion y bienestar.',
            'category' => 'Entrenamiento',
            'date' => '2026-03-10',
            'author' => 'WellCore Team',
            'reading_time' => '8 min',
            'gradient' => 'from-fuchsia-500/20 to-violet-500/10',
            'content' => '<h3>Fisiologia del Ciclo y Entrenamiento</h3>
<p>El ciclo menstrual dura en promedio 28 dias (con variaciones normales de 24-35 dias) y se divide en dos fases principales: la fase folicular (dias 1-14, desde la menstruacion hasta la ovulacion) y la fase lutea (dias 15-28, desde la ovulacion hasta la siguiente menstruacion). Cada fase presenta un perfil hormonal distinto que influye en el metabolismo energetico, la fuerza, la recuperacion y la tolerancia al ejercicio. Investigacion publicada en <em>Sports Medicine</em> ha comenzado a documentar como estos cambios hormonales afectan el rendimiento atletico.</p>

<p>Durante la fase folicular, los niveles de estrogeno aumentan progresivamente. El estrogeno tiene efectos anabolicos, mejora la sensibilidad a la insulina, favorece la utilizacion de carbohidratos como combustible, y puede facilitar la recuperacion. Durante la fase lutea, la progesterona domina, aumentando la temperatura corporal, favoreciendo la oxidacion de grasas, y potencialmente reduciendo la tolerancia a entrenamientos de alta intensidad.</p>

<h3>Entrenamiento en la Fase Folicular</h3>
<p>La fase folicular, particularmente la semana despues de la menstruacion, es generalmente el mejor momento para entrenamiento de alta intensidad. Los niveles crecientes de estrogeno facilitan la ganancia de fuerza, la tolerancia a volumenes altos, y la recuperacion entre sesiones. Estudios preliminares de Wikstrom-Frisen et al. sugieren que concentrar el entrenamiento de alta frecuencia y volumen en esta fase puede producir mayores ganancias en fuerza y masa muscular comparado con una distribucion uniforme a lo largo del ciclo.</p>

<ul>
<li><strong>Fase folicular temprana (dias 1-5, menstruacion):</strong> Entrenar segun la energia disponible. Muchas mujeres se sienten bien entrenando; otras prefieren reducir la intensidad. No hay evidencia de que el ejercicio durante la menstruacion sea perjudicial</li>
<li><strong>Fase folicular tardia (dias 6-14):</strong> Ventana optima para PRs, entrenamiento pesado (1-5 RM), sesiones de alto volumen, y entrenamientos HIIT. Aprovecha la tolerancia aumentada al dolor y la recuperacion mejorada</li>
<li><strong>Ovulacion (dia 14 aprox):</strong> Pico de fuerza pero tambien mayor laxitud ligamentaria por el pico de estrogeno. Algunos estudios sugieren mayor riesgo de lesion de LCA en este periodo — enfocate en tecnica impecable</li>
</ul>

<h3>Entrenamiento en la Fase Lutea</h3>
<p>Durante la fase lutea, la progesterona elevada puede aumentar la percepcion del esfuerzo, reducir la tolerancia al calor, y favorecer un metabolismo mas dependiente de grasas. Esto no significa que debas dejar de entrenar — significa ajustar las expectativas y posiblemente la estructura. Reduce ligeramente el volumen total (10-15%), prioriza entrenamientos de intensidad moderada (6-12 RM), y acepta que los pesos pueden sentirse mas pesados sin que esto indique perdida de fuerza real. La fase lutea tardia (dias 24-28, periodo premenstrual) es donde los sintomas son mas pronunciados.</p>

<h3>Nutricion Ciclica</h3>
<p>Las necesidades nutricionales tambien cambian a lo largo del ciclo. Durante la fase lutea, el metabolismo basal puede aumentar 100-300 kcal/dia. En lugar de luchar contra el hambre aumentada, considera un ligero aumento en calorias provenientes principalmente de grasas saludables y carbohidratos complejos. La ingesta de proteina debe mantenerse constante (1.6-2.2 g/kg). Ademas, incrementar alimentos ricos en magnesio (chocolate oscuro, nueces, espinacas) y omega-3 puede ayudar a manejar la retencion de liquidos y los calambres. Es importante monitorear el peso con promedios semanales, no diarios, ya que la retencion hidrica en la fase lutea puede enmascarar el progreso real.</p>

<p>En WellCore, los planes de entrenamiento para mujeres consideran estas variaciones fisiologicas. No se trata de hacer menos — se trata de hacer lo correcto en el momento correcto para maximizar resultados y bienestar.</p>',
        ],
    ];

    /**
     * Apply locale-appropriate fields to a single article array.
     */
    private static function localizeArticle(array $article): array
    {
        $isEn = app()->getLocale() === 'en';

        if ($isEn) {
            $article['title']    = $article['title_en']    ?? $article['title'];
            $article['excerpt']  = $article['excerpt_en']  ?? $article['excerpt'];
            $article['category'] = $article['category_en'] ?? $article['category'];
            $article['content']  = $article['content_en']  ?? $article['content'];
        }

        return $article;
    }

    /**
     * Get all articles (for the index page), localized to the current locale.
     */
    public static function getArticles(): array
    {
        return collect(self::$articles)
            ->map(fn (array $article) => self::localizeArticle($article))
            ->toArray();
    }

    /**
     * Display a single blog article.
     */
    public function show(string $slug)
    {
        $article = collect(self::$articles)->firstWhere('slug', $slug);

        if (!$article) {
            abort(404);
        }

        $article = self::localizeArticle($article);

        return view('public.blog.show', [
            'article'  => $article,
            'articles' => self::getArticles(),
        ]);
    }
}
