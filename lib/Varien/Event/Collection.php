<?php

/**
 * Collection of events
 *
 * @copyright   Varien (c) 2007 (http://www.varien.com)
 * @license     http://www.opensource.org/licenses/osl-3.0.php
 * @package     Varien
 * @subpackage  Event
 * @author      Moshe Gurvich <moshe@varien.com>
 */
class Varien_Event_Collection
{
    /**
     * Array of events in the collection
     *
     * @var array
     */
    protected $_events;
    
    /**
     * Global observers
     * 
     * For example regex observers will watch all events that 
     *
     * @var Varien_Event_Observer_Collection
     */
    protected $_observers;
    
    /**
     * Initializes global observers collection
     * 
     */
    public function __construct()
    {
        $this->_events = array();
        $this->_globalObservers = new Varien_Event_Observer_Collection();
    }
    
    /**
     * Returns all registered events in collection
     *
     * @return array
     */
    public function getAllEvents()
    {
        return $this->_events;
    }
    
    /**
     * Returns all registered global observers for the collection of events
     *
     * @return Varien_Event_Observer_Collection
     */
    public function getGlobalObservers()
    {
        return $this->_globalObservers;
    }
    
    /**
     * Returns event by its name
     *
     * If event doesn't exist creates new one and returns it
     * 
     * @param string $eventName
     * @return Varien_Event
     */
    public function getEventByName($eventName)
    {
        if (!isset($this->_events[$eventName])) {
            $this->addEvent(new Varien_Event(array('name'=>$eventName)));
        }
        return $this->_events[$eventName];
    }
    
    /**
     * Register an event for this collection
     *
     * @param Varien_Event $event
     * @return Varien_Event_Collection
     */
    public function addEvent(Varien_Event $event)
    {
        $this->_events[$event->getName()] = $event;
        return $this;
    }
    
    /**
     * Register an observer
     * 
     * If observer has event_name property it will be regitered for this specific event.
     * If not it will be registered as global observer
     *
     * @param Varien_Event_Observer $observer
     * @return Varien_Event_Collection
     */
    public function addObserver(Varien_Event_Observer $observer)
    {
        $eventName = $observer->getEventName();
        if ($eventName) {
            $this->getEventByName($eventName)->addObserver($observer);
        } else {
            $this->getGlobalObservers()->addObserver($observer);
        }
        return $this;
    }
    
    /**
     * Dispatch event name with optional data
     *
     * Will dispatch specific event and will try all global observers
     * 
     * @param string $eventName
     * @param array $data
     * @return Varien_Event_Collection
     */
    public function dispatch($eventName, array $data=array())
    {Mage::log($eventName);
        $event = $this->getEventByName($eventName);
        $event->addData($data)->dispatch();
        $this->getGlobalObservers()->dispatch($event);
        return $this;
    }
}