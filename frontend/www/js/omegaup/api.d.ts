declare namespace omegaup.api {
  export interface Session {
    currentSession: () => Promise<{ [name: string]: any }>;
  }
}

const API = {
  Session: omegaup.api.Session,
};

export { API as default };
