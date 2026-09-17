const helpConfig: {
  title: {
    en: string;
    es: string;
    pt: string;
  };
  description: {
    en: string;
    es: string;
    pt: string;
  };
  url: string;
  icon: 'video' | 'chat' | 'news' | 'book' | 'docs' | 'code';
  external: boolean;
}[] = [
  {
    title: {
      en: 'Video Tutorials',
      es: 'Video Tutoriales',
      pt: 'Vídeos Tutoriais',
    },
    description: {
      en:
        'Learn competitive programming through our YouTube playlist of tutorials and problem walkthroughs.',
      es:
        'Aprende programación competitiva a través de nuestra lista de reproducción de tutoriales y explicaciones de problemas.',
      pt:
        'Aprenda programação competitiva através de nossa playlist do YouTube com tutoriais e explicações de problemas.',
    },
    url:
      'https://www.youtube.com/playlist?list=PLdSCJwXErQ8FhVwmlySvab3XtEVdE8QH4',
    icon: 'video',
    external: true,
  },
  {
    title: {
      en: 'Discord Community',
      es: 'Comunidad en Discord',
      pt: 'Comunidade no Discord',
    },
    description: {
      en:
        'Join our active community on Discord to chat with other competitive programmers, ask questions, and share experiences.',
      es:
        'Únete a nuestra comunidad activa en Discord para conversar con otros programadores competitivos, hacer preguntas y compartir experiencias.',
      pt:
        'Junte-se à nossa comunidade ativa no Discord para conversar com outros programadores competitivos, fazer perguntas e compartilhar experiências.',
    },
    url: 'https://discord.com/invite/K3JFd9d3wk',
    icon: 'chat',
    external: true,
  },
  {
    title: {
      en: 'Blog',
      es: 'Blog',
      pt: 'Blog',
    },
    description: {
      en:
        'Read articles and insights about competitive programming, problem-solving techniques, and platform updates on our blog.',
      es:
        'Lee artículos e información sobre programación competitiva, técnicas de resolución de problemas y actualizaciones de la plataforma en nuestro blog.',
      pt:
        'Leia artigos e informações sobre programação competitiva, técnicas de resolução de problemas e atualizações da plataforma em nosso blog.',
    },
    url: 'https://blog.omegaup.com/',
    icon: 'news',
    external: true,
  },
  {
    title: {
      en: 'Algorithms Handbook',
      es: 'Manual de Algoritmos',
      pt: 'Manual de Algoritmos',
    },
    description: {
      en:
        'Access the comprehensive algorithms handbook designed to help you master essential algorithms and data structures for competitive programming.',
      es:
        'Accede al comprehensive manual de algoritmos diseñado para ayudarte a dominar algoritmos y estructuras de datos esenciales para programación competitiva.',
      pt:
        'Acesse o comprehensive manual de algoritmos projetado para ajudá-lo a dominar algoritmos e estruturas de dados essenciais para programação competitiva.',
    },
    url:
      'https://drive.google.com/file/d/1PLOO3wLCnOVC_cODwiofahsRGeyoJeCU/view',
    icon: 'book',
    external: true,
  },
  {
    title: {
      en: 'Documentation',
      es: 'Documentación',
      pt: 'Documentação',
    },
    description: {
      en:
        'Explore the complete documentation for the omegaUp platform, including detailed guides on problem solving and contest participation.',
      es:
        'Explora la documentación completa de la plataforma omegaUp, incluyendo guías detalladas sobre resolución de problemas y participación en concursos.',
      pt:
        'Explore a documentação completa da plataforma omegaUp, incluindo guias detalhados sobre resolução de problemas e participação em concursos.',
    },
    url: '/docs/',
    icon: 'docs',
    external: false,
  },
  {
    title: {
      en: 'GitHub Repository',
      es: 'Repositorio en GitHub',
      pt: 'Repositório no GitHub',
    },
    description: {
      en:
        'Contribute to omegaUp by exploring the source code on GitHub. Report issues, submit pull requests, and help improve the platform.',
      es:
        'Contribuye a omegaUp explorando el código fuente en GitHub. Reporta problemas, envía Pull Requests y ayuda a mejorar la plataforma.',
      pt:
        'Contribua para omegaUp explorando o código-fonte no GitHub. Reporte problemas, envie solicitações de pull e ajude a melhorar a plataforma.',
    },
    url: 'https://github.com/omegaup/omegaup',
    icon: 'code',
    external: true,
  },
];

export default helpConfig;
