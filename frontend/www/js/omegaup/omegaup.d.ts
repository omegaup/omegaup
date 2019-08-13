declare namespace omegaup {
  export interface Experiments {
  };

  export interface EventListenerList {
  };

  export interface OmegaUp {
    addError: (error: any) => void;
    convertTimes: (item: any) => any;
    experiments?: omegaup.Experiments;
    loggedIn: boolean;
    on: (events: string, handler: () => void) => void;
    ready: boolean;
    remoteTime: (timestamp: number, options: {}) => Date;
    username?: string;
  };
}

export var T: { [translationString: string]: string; };

export var OmegaUp: omegaup.OmegaUp;
