module.exports = {
  editor: {
    setModelLanguage: jest.fn(),
    create: jest.fn(() => ({
      getModel: jest.fn(() => ({
        onDidChangeContent: jest.fn(),
        getValue: jest.fn(),
        setValue: jest.fn(),
      })),
      layout: jest.fn(),
      updateOptions: jest.fn(),
    })),
    createModel: jest.fn(() => ({
      setValue: jest.fn(),
    })),
    createDiffEditor: jest.fn(() => ({
      setModel: jest.fn(),
      layout: jest.fn(),
    })),
  },
};
