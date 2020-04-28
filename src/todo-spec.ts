import { browser, by, element } from 'protractor';

describe('angularjs homepage todo list', () => {
  it('should add a todo', () => {
    browser.get('https://angularjs.org');

    element(by.model('todoList.todoText')).sendKeys(
      'write first protractor test'
    );
    element(by.css('[value="add"]')).click();

    const todoList = element.all(by.repeater('todo in todoList.todos'));
    expect(todoList.count()).toEqual(3);
    expect(todoList.get(2).getText()).toEqual('write first protractor test');

    // You wrote your first test, cross it off the list
    todoList.get(2).element(by.css('input')).click();
    const completedAmount = element.all(by.css('.done-true'));
    expect(completedAmount.count()).toEqual(2);
  });
});

describe('Protractor Demo App', () => {
  it('should have a title', () => {
    browser.get('http://juliemr.github.io/protractor-demo/');

    expect(browser.getTitle()).toEqual('Super Calculator');
  });
});
