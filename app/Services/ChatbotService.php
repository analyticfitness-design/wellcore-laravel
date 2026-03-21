<?php

namespace App\Services;

class ChatbotService
{
    private array $responses = [
        [
            'keywords' => ['plan', 'precio', 'precios', 'costo', 'cuanto cuesta', 'cuanto vale', 'mensualidad', 'tarifa'],
            'response' => 'Ofrecemos 3 planes: Esencial ($299,000 COP/mes), Metodo ($399,000 COP/mes) y Elite ($549,000 COP/mes). Cada uno con diferentes niveles de acompanamiento. Puedes verlos en detalle en /planes',
        ],
        [
            'keywords' => ['metodo', 'como funciona', 'metodologia', 'sistema', 'enfoque'],
            'response' => 'El Metodo WellCore se basa en 4 pilares: entrenamiento personalizado, nutricion flexible, coaching conductual y comunidad. No es una dieta ni una rutina generica, es un sistema integral adaptado a tu vida. Conoce mas en /metodo',
        ],
        [
            'keywords' => ['rise', 'reto', 'programa 12', 'doce semanas', '12 semanas', 'transformacion'],
            'response' => 'RISE es nuestro programa de transformacion de 12 semanas. Incluye plan de entrenamiento, guia nutricional, coaching semanal y acceso a comunidad exclusiva. Es ideal si quieres resultados visibles en poco tiempo. Info en /reto-rise',
        ],
        [
            'keywords' => ['nutricion', 'dieta', 'macros', 'calorias', 'alimentacion', 'comida', 'comer'],
            'response' => 'Nuestro enfoque nutricional es flexible y sostenible. No eliminamos grupos de alimentos. Tu coach te ayuda a entender macros, porciones y habitos alimenticios adaptados a tus gustos y cultura. Sin dietas extremas.',
        ],
        [
            'keywords' => ['entrenamiento', 'ejercicio', 'gym', 'gimnasio', 'rutina', 'workout', 'entrenar'],
            'response' => 'Cada plan de entrenamiento se adapta a tu nivel, equipamiento disponible y objetivos. Puedes entrenar en gym o en casa. Tu coach ajusta la rutina segun tu progreso cada semana.',
        ],
        [
            'keywords' => ['coach', 'coaches', 'entrenador', 'asesor', 'acompanamiento'],
            'response' => 'Cada cliente tiene un coach personal certificado que lo acompana durante todo el proceso. Tu coach revisa check-ins, ajusta planes y esta disponible para resolver dudas. Conoce al equipo en /coaches',
        ],
        [
            'keywords' => ['resultado', 'tiempo', 'cuanto tardo', 'cuanto demora', 'cuando veo', 'progreso'],
            'response' => 'Los resultados varian segun consistencia y punto de partida. La mayoria de clientes ven cambios notables en 4-6 semanas. Cambios significativos suelen darse entre 8-12 semanas con adherencia al plan.',
        ],
        [
            'keywords' => ['cancelar', 'cancelacion', 'dar de baja', 'desuscribir'],
            'response' => 'Puedes cancelar tu suscripcion en cualquier momento desde tu perfil o escribiendonos a info@wellcorefitness.com. No hay permanencia minima ni penalidades. La cancelacion aplica al final del periodo pagado.',
        ],
        [
            'keywords' => ['pago', 'pagar', 'tarjeta', 'transferencia', 'wompi', 'metodo de pago', 'nequi', 'daviplata'],
            'response' => 'Aceptamos tarjetas de credito/debito, PSE y Nequi a traves de Wompi (plataforma segura). Tambien puedes pagar por transferencia bancaria. El cobro es mensual y automatico. Inscribete en /inscripcion',
        ],
        [
            'keywords' => ['experiencia', 'principiante', 'nivel', 'novato', 'nunca he', 'primera vez', 'empezando'],
            'response' => 'No necesitas experiencia previa! Nuestros planes se adaptan a cualquier nivel, desde principiantes absolutos hasta avanzados. Tu coach te guia paso a paso y ajusta todo a tu capacidad actual.',
        ],
        [
            'keywords' => ['lesion', 'dolor', 'lastimado', 'operacion', 'cirugia', 'limitacion'],
            'response' => 'Si tienes alguna lesion o condicion medica, es importante que nos lo compartas. Tu coach adaptara el entrenamiento para evitar molestias. Siempre recomendamos tener autorizacion medica antes de iniciar.',
        ],
        [
            'keywords' => ['suplemento', 'proteina', 'creatina', 'vitamina', 'producto'],
            'response' => 'No vendemos suplementos ni los requerimos. Nuestro enfoque se basa en alimentacion real. Si tu coach considera que algun suplemento podria ayudarte, te lo recomendara de forma transparente.',
        ],
        [
            'keywords' => ['contacto', 'email', 'whatsapp', 'telefono', 'escribir', 'comunicar'],
            'response' => 'Puedes contactarnos por email a info@wellcorefitness.com o por WhatsApp al +57 316 5250000. Tambien puedes agendar una consulta gratuita desde /inscripcion. Respondemos en menos de 24 horas.',
        ],
        [
            'keywords' => ['inscripcion', 'empezar', 'comenzar', 'inscribir', 'unirme', 'registrar', 'iniciar', 'quiero'],
            'response' => 'Para inscribirte, ve a /inscripcion y completa el formulario. Recibes una llamada de bienvenida con tu coach asignado y empiezas en menos de 48 horas. Tambien puedes agendar una consulta gratuita primero!',
        ],
        [
            'keywords' => ['hola', 'buenos dias', 'buenas tardes', 'buenas noches', 'hey', 'hi', 'hello', 'que tal'],
            'response' => 'Hola! Bienvenido a WellCore. Estoy aqui para ayudarte con informacion sobre nuestros planes, el metodo, precios o cualquier duda. Que te gustaria saber?',
        ],
        [
            'keywords' => ['gracias', 'thanks', 'genial', 'excelente', 'perfecto', 'chevere', 'buenisimo'],
            'response' => 'Con gusto! Si tienes mas preguntas no dudes en escribirme. Tambien puedes agendar una consulta gratuita en /inscripcion para hablar con un asesor.',
        ],
        [
            'keywords' => ['presencial', 'bogota', 'en persona', 'cara a cara', 'sede', 'gimnasio wellcore'],
            'response' => 'Ofrecemos entrenamiento presencial en Bogota con sesiones individuales y grupos reducidos. Puedes conocer los detalles y horarios en /presencial o inscribirte directamente.',
        ],
        [
            'keywords' => ['reembolso', 'devolucion', 'dinero', 'garantia'],
            'response' => 'Contamos con una politica de reembolso dentro de los primeros 7 dias si no estas satisfecho. Puedes consultar los terminos completos en /reembolsos o escribirnos a info@wellcorefitness.com',
        ],
        [
            'keywords' => ['app', 'aplicacion', 'plataforma', 'donde veo', 'acceso'],
            'response' => 'Todo se gestiona desde nuestra plataforma web, accesible desde cualquier dispositivo. Al inscribirte recibes tus credenciales para acceder a tu dashboard con plan, metricas, chat con coach y mas.',
        ],
    ];

    public function getResponse(string $message): string
    {
        $message = mb_strtolower(trim($message));

        foreach ($this->responses as $entry) {
            foreach ($entry['keywords'] as $keyword) {
                if (str_contains($message, $keyword)) {
                    return $entry['response'];
                }
            }
        }

        return $this->getDefaultResponse();
    }

    private function getDefaultResponse(): string
    {
        return 'No tengo una respuesta especifica para eso, pero puedo ayudarte con informacion sobre planes, precios, el metodo, nutricion o entrenamiento. Tambien puedes escribirnos a info@wellcorefitness.com o agendar una consulta gratuita en /inscripcion';
    }
}
