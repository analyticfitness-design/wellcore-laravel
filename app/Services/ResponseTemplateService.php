<?php

namespace App\Services;

class ResponseTemplateService
{
    /**
     * Get all response templates organized by context.
     *
     * @param  string|null  $context  Filter by context: 'checkin', 'video', 'ticket', 'message', 'notes', or null for all
     * @return array<string, array<int, array{title: string, body: string}>>
     */
    public static function getTemplates(?string $context = null): array
    {
        $templates = [
            'checkin' => [
                'label' => 'Check-in',
                'templates' => [
                    ['title' => 'Buen progreso', 'body' => "Excelente trabajo esta semana. Tu consistencia se nota en los resultados. Sigue con el plan tal como esta y veremos mejoras aun mayores la proxima semana."],
                    ['title' => 'Ajustar nutricion', 'body' => "Buen esfuerzo en el entrenamiento. Note que la nutricion puede mejorar. Recuerda priorizar proteina en cada comida (30-40g) y mantener tu ingesta calorica estable. Revisemos juntos tu plan nutricional."],
                    ['title' => 'Mas descanso', 'body' => "Tu RPE esta alto y el bienestar bajo. Necesitas priorizar el descanso esta semana. Reduce la intensidad un 20% y asegurate de dormir 7-8 horas. La recuperacion es parte del entrenamiento."],
                    ['title' => 'Motivacion', 'body' => "Entiendo que esta semana fue dificil. Recuerda que la consistencia a largo plazo es mas importante que la perfeccion. Un mal dia no arruina semanas de buen trabajo. Sigamos adelante."],
                    ['title' => 'Felicitar PR', 'body' => "Felicitaciones por tu nuevo record personal. Eso demuestra que el programa esta funcionando. Toma nota de como te sentiste y sigamos construyendo sobre esto."],
                    ['title' => 'Ajustar volumen', 'body' => "He revisado tus numeros y creo que podemos ajustar el volumen de entrenamiento. Voy a modificar tu plan para la proxima semana. Mantente atento a los cambios en tu dashboard."],
                    ['title' => 'Semana de descarga', 'body' => "Basado en tus ultimas semanas de entrenamiento, te programare una semana de descarga. Reduce el peso al 60-70% y enfocate en la tecnica. Tu cuerpo necesita recuperarse para seguir progresando."],
                ],
            ],
            'video' => [
                'label' => 'Video Check-in',
                'templates' => [
                    ['title' => 'Buena tecnica', 'body' => "Muy buena ejecucion. La tecnica se ve solida y el rango de movimiento es completo. Sigue asi y cuando te sientas comodo, podemos subir la carga progresivamente."],
                    ['title' => 'Corregir forma', 'body' => "Gracias por el video. Noto algunos puntos a mejorar en la tecnica. Enfocate en mantener la espalda neutra y controlar la fase excentrica (bajada). Te envio un video de referencia."],
                    ['title' => 'Ajustar tempo', 'body' => "El peso se ve bien pero necesitas controlar mas el tempo. Intenta 3 segundos en la bajada, pausa de 1 segundo abajo, y subida explosiva. Esto mejorara la hipertrofia."],
                    ['title' => 'Reducir peso', 'body' => "Noto que el peso esta comprometiendo tu tecnica. Reduce un 10-15% y enfocate en sentir el musculo trabajar. Mejor calidad de repeticiones con menos peso que malas reps con mas."],
                    ['title' => 'Rango de movimiento', 'body' => "Necesitas trabajar en el rango de movimiento completo. Intenta llegar mas abajo en la fase excentrica. Si la movilidad es un problema, agreguemos ejercicios de estiramiento al inicio de tu sesion."],
                ],
            ],
            'ticket' => [
                'label' => 'Tickets',
                'templates' => [
                    ['title' => 'Recibido', 'body' => "Hemos recibido tu solicitud y la estamos revisando. Te responderemos en las proximas 24 horas con una solucion."],
                    ['title' => 'Cambio de plan', 'body' => "He actualizado tu plan segun lo solicitado. Los cambios ya estan visibles en tu dashboard. Si tienes alguna duda sobre los ejercicios, no dudes en preguntar."],
                    ['title' => 'Problema tecnico', 'body' => "Gracias por reportar este problema. Nuestro equipo tecnico ya esta trabajando en la solucion. Te notificaremos cuando este resuelto."],
                    ['title' => 'Cambio de rutina', 'body' => "He realizado los cambios en tu rutina de entrenamiento. La nueva version ya esta disponible en tu dashboard. Revisa los ejercicios y avisame si necesitas algun ajuste adicional."],
                    ['title' => 'Consulta nutricion', 'body' => "He revisado tu consulta nutricional. Tus macros actuales estan bien distribuidos. Te sugiero hacer los siguientes ajustes menores para optimizar resultados."],
                    ['title' => 'Resuelto', 'body' => "El problema ha sido resuelto. Por favor verifica que todo este funcionando correctamente. Si el problema persiste, no dudes en abrir un nuevo ticket."],
                ],
            ],
            'message' => [
                'label' => 'Mensajes',
                'templates' => [
                    ['title' => 'Bienvenida', 'body' => "Bienvenido/a a WellCore! Estoy aqui para guiarte en tu transformacion. Revisa tu plan de entrenamiento y no dudes en escribirme si tienes preguntas."],
                    ['title' => 'Recordatorio check-in', 'body' => "Recuerda completar tu check-in semanal para que pueda evaluar tu progreso y ajustar el plan si es necesario. Tu feedback es clave para tus resultados."],
                    ['title' => 'Felicitacion general', 'body' => "Queria felicitarte por tu compromiso y dedicacion. Los resultados se estan notando y quiero que sepas que tu esfuerzo vale la pena. Sigue asi!"],
                    ['title' => 'Recordatorio rutina', 'body' => "Recuerda que tu nueva rutina ya esta disponible en el dashboard. Revisa los ejercicios y avisame si tienes alguna duda antes de empezar."],
                    ['title' => 'Seguimiento semanal', 'body' => "Como vas con el entrenamiento esta semana? Queria hacer un seguimiento rapido. Cuentame como te has sentido y si has tenido algun problema con los ejercicios."],
                    ['title' => 'Disponibilidad', 'body' => "Estoy disponible para resolver cualquier duda que tengas. Puedes escribirme por aqui o crear un ticket si necesitas algo especifico. Estamos para ayudarte."],
                ],
            ],
            'notes' => [
                'label' => 'Notas',
                'templates' => [
                    ['title' => 'Seguimiento progreso', 'body' => "Cliente muestra progreso constante en las ultimas semanas. Adherencia al plan >80%. Considerar aumento progresivo de cargas la proxima semana."],
                    ['title' => 'Alerta adherencia', 'body' => "Adherencia al plan por debajo del 60% las ultimas 2 semanas. Programar seguimiento personalizado y evaluar posibles ajustes al plan."],
                    ['title' => 'Logro destacado', 'body' => "Cliente alcanzo un nuevo PR. Excelente progresion desde el inicio del programa. Documentar para revision trimestral."],
                    ['title' => 'Ajuste nutricional', 'body' => "Basado en los check-ins recientes, el cliente necesita ajustar su ingesta calorica. Revisar macros y actualizar plan nutricional."],
                    ['title' => 'Nota de sesion', 'body' => "Sesion de seguimiento realizada. Se revisaron objetivos y se ajusto el plan de entrenamiento. Cliente motivado y comprometido con los cambios."],
                ],
            ],
        ];

        if ($context && isset($templates[$context])) {
            return [$context => $templates[$context]];
        }

        return $templates;
    }

    /**
     * Get templates formatted for a specific Livewire property binding.
     *
     * @param  string  $context  Template context
     * @return array  Flat array of templates for the given context
     */
    public static function getTemplatesForContext(string $context): array
    {
        $all = self::getTemplates($context);
        return $all[$context]['templates'] ?? [];
    }
}
